<?php

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ApiUser
{
	function register(Application $app)
	{
		$username = trim($app['request']->request->get('username', NULL));
		$email = trim($app['request']->request->get('email', NULL));
		$password = trim($app['request']->request->get('password', NULL));

		$data = array();

		do
		{
			if(! $username || ! $email || ! $password)
			{
				$data['error'] = 'Datos insuficientes';
				break;
			}

			if(! $app['validator']->isEmail($email))
			{
				$data['error'] = 'Debes especificar un email valido';
				break;
			}

			if(! $app['validator']->isAlphaNumeric($username, false, true) || strlen($username) < 6 || strlen($username) > 16)
			{
				$data['error'] = 'Debes especificar un nombre de usuario valido';
				break;
			}

			if(! $app['validator']->isAlphaNumeric($password, false, false) || strlen($password) != 64)
			{
				$data['error'] = 'Debes especificar una contraseña valida';
				break;
			}

			$sql = "SELECT user_id from user where username like '$username'";
			
			if($app['sql']->hasResults($sql))
			{
				$data['error'] = 'Ya existe un usuario con ese nombre';
				break;
			}

			$sql = "SELECT user_id from user where email like '$email'";
			
			if($app['sql']->hasResults($sql))
			{
				$data['error'] = 'Ya existe un usuario con ese email';
				break;
			}

			$password .= $app['security.salt'];
			$password = hash('sha256', $password);

			$sql = "INSERT into user (username, password, email, active)
				values ('$username','$password','$email', 0)";
			$user_id = $app['sql']->runInsert($sql);

			if(! $user_id)
			{
				$data['error'] = 'Ha ocurrido un error al crear el usuario';
				break;
			}

			$sql = "SELECT token_type_id from token_type where name = 'user_active'";
			$token_type_id = $app['sql']->getValue($sql);

			$token = $app['auth']->generateToken();

			$sql = "INSERT into token (token, user_id, token_type_id, date_expire, active)
				values ('$token', $user_id, $token_type_id, FROM_UNIXTIME(" . strtotime("+1 week") . "), 1)";
			$token_id = $app['sql']->runInsert($sql);
			
			$user_email = $app['sql']->getValue("SELECT email from user where user_id = $user_id");
			$user_active_path = $app['url_generator']->generate('rt_usr_active', array('token' => $token));

			$email_dev = $app['email.sender'];

			$email_msg = \Swift_Message::newInstance();
			$email_msg->setSubject("Activar cuenta")
				->setFrom($email_dev)
				->setSender($email_dev)
				->setReplyTo($email_dev)
				->setReturnPath($email_dev)
				->setTo($user_email)
				->setMaxLineLength(1000) // Nunca mayor de 1000 líneas! (RFC 2822)
				->setPriority(2) // Highest (1), High (2), Normal (3), Low (4), Lowest (5)
				->setContentType('text/html')
				->setCharset('utf-8')
				->setBody("<!DOCTYPE html><html><body><pre>Activar cuenta:\n\n$user_active_path</pre></body></html>", 'text/html');
			$app['mailer']->send($email_msg);

			$data['msg'] = 'ok';
		}
		while(0);
		
		return $app->json($data, (empty($data['error']) ? 200 : 400));
	}

	function active(Application $app, $token)
	{
		$data = array();
		
		do
		{
			$token = $app['sql']->toSQL($token, false);

			$sql = "SELECT token_id, user_id, active
				from token
				where token_type_id = (select token_type_id from token_type where name = 'user_active')
					and date_expire > NOW()
					and token = $token";
			$row = $app['sql']->getFirstRow($sql);

			if(! $row)
			{
				$data['error'] = 'Token no válido';
				break;
			}

			if($row['active'] == 0)
			{
				$data['error'] = 'Este token ya ha sido utilizado anteriormente (Deberías tener ya tu cuenta activada)';
				break;
			}

			$sql = "UPDATE user set active = 1 where user_id = " . $row['user_id'];
			$app['sql']->run($sql);

			$sql = "UPDATE token set active = 0 where token_id = " . $row['token_id'];
			$app['sql']->run($sql);

			$data['msg'] = 'ok';
		}
		while(0);

		return $app->json($data, (empty($data['error']) ? 200 : 400));
	}

	function login(Application $app)
	{
		$user = trim($app['request']->request->get('username', NULL));
		$password = trim($app['request']->request->get('password', NULL));

		$data = array();

		do
		{
			if($app['validator']->isEmpty($user) || $app['validator']->isEmpty($password))
			{
				$data['error'] = 'Usuario y/o contraseña no especificados';
				break;
			}

			$password .= $app['security.salt'];
			$password = hash('sha256', $password);

			$sql = "SELECT user_id, username, email, active
				from user
				where (username = '$user' or email = '$user') and password = '$password'
				limit 1";

			if($app['sql']->hasNotResults($sql))
			{
				$data['error'] = 'Usuario y/o contraseña incorrectos';
				break;
			}

			$subRequest = Request::create($app['url_generator']->generate('rta_usr_logout'), 'POST');
			$app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

			$user_data = $app['sql']->getFirstRow($sql);
			
			if($user_data['active'] == 0)
			{
				$data['error'] = 'Debes activar el usuario antes de iniciar sesión. Revisa la bandeja de entrada de tu correo y en spam.';
				break;
			}

			$app['session']->start();
			$app['session']->set('session.version', $app['session.version']);

			$app['session']->set('user.id',            $user_data['user_id']);
			$app['session']->set('user.username',      $user_data['username']);
			$app['session']->set('user.email',         $user_data['email']);
			$app['session']->set('user_real.id',       $user_data['user_id']);
			$app['session']->set('user_real.username', $user_data['username']);
			$app['session']->set('user_real.email',    $user_data['email']);
			
			if($app['request']->request->get('remindme', NULL))
			{
				$app['session']->migrate(false, (30*24*60*60)); // 30 días
			}

			$app['monolog']->addNotice("Sesión iniciada -> Usuario: $user (ID: {$user_data['user_id']})");
			
			$data['msg'] = 'ok';
		}
		while(0);

		return $app->json($data, (empty($data['error']) ? 200 : 400));

		// -------------------------------------------------------------------------------------------------------------------------------
		// TODO: Obtener los roles del usuario (permisos)
		/*
		$data_user_roles = UtilData::get_user_roles($app, $user_data['user_id']);
		$user_roles = array();

		foreach ($data_user_roles as $rol)
		{
			$user_roles[] = $rol['value'];
		}

		$app['session']->set('user.rol', $user_roles);
		$app['session']->set('user_real.rol', $user_roles);

		$data_user_menu = UtilData::get_user_menu_roles($app, $user_data['user_id']);
		$user_menu = array();

		foreach ($data_user_menu as $menu)
		{
			$user_menu[] = $menu['value'];
		}

		$app['session']->set('user.menu', $user_menu);
		*/
		// -------------------------------------------------------------------------------------------------------------------------------
	}

	function password_change(Application $app)
	{
		$data = array();

		$old_password = trim($app['request']->request->get('old_password', NULL));
		$password = trim($app['request']->request->get('password', NULL));

		do
		{
			if(! $app['auth']->isAuthenticated())
			{
				$data['error'] = 'Debes estar autentificado para realizar esta accion';
				break;
			}

			if(! $old_password || ! $password)
			{
				$data['error'] = 'Datos insuficientes';
				break;
			}

			if(! $app['validator']->isAlphaNumeric($old_password, false, false) || strlen($old_password) != 64
				|| ! $app['validator']->isAlphaNumeric($password, false, false) || strlen($password) != 64)
			{
				$data['error'] = 'Debes especificar una contraseña valida';
				break;
			}

			$user_id = $app['session']->get('user_real.id');

			$old_password .= $app['security.salt'];
			$old_password = hash('sha256', $old_password);

			$sql = "SELECT user_id from user
				where user_id = $user_id
					and password = '$old_password'";
			
			if($app['sql']->hasNotResults($sql))
			{
				$data['error'] = 'Contraseña anterior incorrecta';
				break;
			}

			$password .= $app['security.salt'];
			$password = hash('sha256', $password);

			$sql = "UPDATE user set password = '$password' where user_id = $user_id";
			$app['sql']->run($sql);
			
			$data['msg'] = 'ok';
		}
		while(0);

		return $app->json($data, (empty($data['error']) ? 200 : 400));
	}

	function password_change_token(Application $app, $token)
	{
		$data = array();

		$password = trim($app['request']->request->get('password', NULL));

		do
		{
			$token = $app['sql']->toSQL($token, false);

			$sql = "SELECT token_id, user_id, active
				from token
				where token_type_id = (select token_type_id from token_type where name = 'user_password_change')
					and date_expire > NOW()
					and token = $token";
			$row = $app['sql']->getFirstRow($sql);

			if(! $row)
			{
				$data['error'] = 'Token no válido';
				break;
			}

			if(! $password)
			{
				$data['error'] = 'Datos insuficientes';
				break;
			}

			if(! $app['validator']->isAlphaNumeric($password, false, false) || strlen($password) != 64)
			{
				$data['error'] = 'Debes especificar una contraseña valida';
				break;
			}
			
			$password .= $app['security.salt'];
			$password = hash('sha256', $password);
			
			$sql = "UPDATE user set active = 1, password = '$password' where user_id = " . $row['user_id'];
			$app['sql']->run($sql);

			$sql = "UPDATE token set active = 0 where token_id = " . $row['token_id'];
			$app['sql']->run($sql);

			$data['msg'] = 'ok';
		}
		while(0);

		return $app->json($data, (empty($data['error']) ? 200 : 400));
	}

	function password_change_token_check(Application $app, $token)
	{
		$data = array();
		
		do
		{
			$token = $app['sql']->toSQL($token, false);

			$sql = "SELECT token_id, user_id, active
				from token
				where token_type_id = (select token_type_id from token_type where name = 'user_password_change')
					and date_expire > NOW()
					and token = $token";
			$row = $app['sql']->getFirstRow($sql);

			if($row)
			{
				if($row['active'] == 0)
				{
					$data['error'] = 'Este token ya ha sido utilizado anteriormente';
					break;
				}
			}
			else
			{
				$data['error'] = 'Token no válido';
				break;
			}

			$data['msg'] = 'ok';
		}
		while(0);

		return $app->json($data, (empty($data['error']) ? 200 : 400));
	}

	function password_forgot(Application $app)
	{
		$data = array();

		$username = trim($app['request']->request->get('username', NULL));

		do
		{
			if(! $app['validator']->isAlphaNumeric($username, true))
			{
				$data['error'] = 'Usuario no valido';
			}

			$sql = "SELECT user_id
				from user
				where email = '$username' or username = '$username'";
			$user_id = $app['sql']->getValue($sql);

			if(! $user_id)
			{
				$data['error'] = 'Usuario no encontrado';
				break;
			}

			$sql = "SELECT token_type_id from token_type where name = 'user_password_change'";
			$token_type_id = $app['sql']->getValue($sql);

			$token = $app['auth']->generateToken();

			$sql = "INSERT into token (token, user_id, token_type_id, date_expire, active)
				values ('$token', $user_id, $token_type_id, FROM_UNIXTIME(" . strtotime("+1 week") . "), 1)";
			$token_id = $app['sql']->runInsert($sql);

			$user_email = $app['sql']->getValue("SELECT email from user where user_id = $user_id");
			$password_change_path = $app['url_generator']->generate('rt_usr_password_change_token', array('token' => $token));

			$email_dev = $app['email.sender'];
			
			$email_msg = \Swift_Message::newInstance();
			$email_msg->setSubject("Cambiar contraseña")
				->setFrom($email_dev)
				->setSender($email_dev)
				->setReplyTo($email_dev)
				->setReturnPath($email_dev)
				->setTo($user_email)
				->setMaxLineLength(1000) // Nunca mayor de 1000 líneas! (RFC 2822)
				->setPriority(2) // Highest (1), High (2), Normal (3), Low (4), Lowest (5)
				->setContentType('text/html')
				->setCharset('utf-8')
				->setBody("<!DOCTYPE html><html><body><pre>Cambiar contraseña:\n\n$password_change_path</pre></body></html>", 'text/html');
			$app['mailer']->send($email_msg);

			$data['msg'] = 'ok';
		}
		while(0);
		
		return $app->json($data, (empty($data['error']) ? 200 : 400));
	}

	function logout(Application $app)
	{
		$user_id = $app['session']->get('user_real.id');
		$user_username = $app['session']->get('user_real.username');

		if($user_id)
		{
			$app['monolog']->addNotice("Sesión cerrada  -> Usuario: $user_username (ID: $user_id)");	
		}
		
		$app['session']->clear(); // Redundante, pero por si acaso...
		$app['session']->invalidate();

		return $app->json(array('msg' => 'ok'), 200);
	}
}
