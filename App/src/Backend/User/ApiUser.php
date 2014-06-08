<?php

namespace Src\Backend\User;

use Src\Lib\ControllerBase;

class ApiUser extends ControllerBase
{
	function register()
	{
		$username = trim($this->requestData('POST', 'username', NULL));
		$email = trim($this->requestData('POST', 'email', NULL));
		$password = trim($this->requestData('POST', 'password', NULL));

		$data = array();

		do
		{
			if(! $username || ! $email || ! $password)
			{
				$data['error'] = 'Datos insuficientes';
				break;
			}

			if(! $this->validator()->isEmail($email))
			{
				$data['error'] = 'Debes especificar un email valido';
				break;
			}

			if(! $this->validator()->isAlphaNumeric($username, false, true) || strlen($username) < 6 || strlen($username) > 16)
			{
				$data['error'] = 'Debes especificar un nombre de usuario valido';
				break;
			}

			if(! $this->validator()->isAlphaNumeric($password, false, false) || strlen($password) != 64)
			{
				$data['error'] = 'Debes especificar una contraseña valida';
				break;
			}

			$sql = "SELECT user_id from user where username like '$username'";

			if($this->db()->hasResults($sql))
			{
				$data['error'] = 'Ya existe un usuario con ese nombre';
				break;
			}

			$sql = "SELECT user_id from user where email like '$email'";

			if($this->db()->hasResults($sql))
			{
				$data['error'] = 'Ya existe un usuario con ese email';
				break;
			}

			$password .= $this->app()['security.salt'];
			$password = hash('sha256', $password);

			$sql = "INSERT into user (username, password, email, active)
				values ('$username','$password','$email', 0)";
			$user_id = $this->db()->runInsert($sql);

			if(! $user_id)
			{
				$data['error'] = 'Ha ocurrido un error al crear el usuario';
				break;
			}

			$sql = "SELECT token_type_id from token_type where name = 'user_active'";
			$token_type_id = $this->db()->getValue($sql);

			$token = $this->auth()->generateToken();

			$sql = "INSERT into token (token, user_id, token_type_id, date_expire, active)
				values ('$token', $user_id, $token_type_id, FROM_UNIXTIME(" . strtotime("+1 week") . "), 1)";
			$token_id = $this->db()->runInsert($sql);

			$user_email = $this->db()->getValue("SELECT email from user where user_id = $user_id");
			$user_active_path = $this->generateUrl('rt_usr_active', array('token' => $token));

			$email_dev = $this->app()['email.sender'];

			try
			{
				$this->email()->setSubject("Activar cuenta");
				$this->email()->setFrom(array($email_dev));
				$this->email()->setSender(array($email_dev));
				$this->email()->setReplyTo(array($email_dev));
				$this->email()->setReturnPath($email_dev);
				$this->email()->setTo(array($user_email));
				$this->email()->setBody("<!DOCTYPE html><html><body><pre>Activar cuenta:\n\n$user_active_path</pre></body></html>");
				$this->email()->send();
			}
			catch(\Exception $e)
			{
				$data['error'] = 'Cuenta creada, pero ha ocurrido un error al enviar el email de activacion de la cuenta';
				break;
			}

			$data['msg'] = 'ok';
		}
		while(0);

		return $this->jsonResponse($data, (empty($data['error']) ? 200 : 400));
	}

	function active($token)
	{
		$data = array();

		do
		{
			$token = $this->db()->toSQL($token, false);

			$sql = "SELECT token_id, user_id, active
				from token
				where token_type_id = (select token_type_id from token_type where name = 'user_active')
					and date_expire > NOW()
					and token = $token";
			$row = $this->db()->getFirstRow($sql);

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
			$this->db()->run($sql);

			$sql = "UPDATE token set active = 0 where token_id = " . $row['token_id'];
			$this->db()->run($sql);

			$data['msg'] = 'ok';
		}
		while(0);

		return $this->jsonResponse($data, (empty($data['error']) ? 200 : 400));
	}

	function login()
	{
		$user = trim($this->requestData('POST', 'username', NULL));
		$password = trim($this->requestData('POST', 'password', NULL));

		$data = array();

		do
		{
			if($this->validator()->isEmpty($user) || $this->validator()->isEmpty($password))
			{
				$data['error'] = 'Usuario y/o contraseña no especificados';
				break;
			}

			$password .= $this->app()['security.salt'];
			$password = hash('sha256', $password);

			$sql = "SELECT user_id, username, email, active
				from user
				where (username = '$user' or email = '$user') and password = '$password'
				limit 1";

			if($this->db()->hasNotResults($sql))
			{
				$data['error'] = 'Usuario y/o contraseña incorrectos';
				break;
			}

			$this->subRequestRoute('rta_usr_logout');

			$user_data = $this->db()->getFirstRow($sql);

			if($user_data['active'] == 0)
			{
				$data['error'] = 'Debes activar el usuario antes de iniciar sesión. Revisa la bandeja de entrada de tu correo y en spam.';
				break;
			}

			$this->session()->start();
			$this->setSessionData('session.version',    $this->app()['session.version']);
			$this->setSessionData('user.id',            $user_data['user_id']);
			$this->setSessionData('user.username',      $user_data['username']);
			$this->setSessionData('user.email',         $user_data['email']);
			$this->setSessionData('user_real.id',       $user_data['user_id']);
			$this->setSessionData('user_real.username', $user_data['username']);
			$this->setSessionData('user_real.email',    $user_data['email']);

			if($this->requestData('POST', 'remindme', NULL))
			{
				$this->session()->migrate(false, (30*24*60*60)); // 30 días
			}

			$this->log()->addNotice("Sesión iniciada -> Usuario: $user (ID: {$user_data['user_id']})");

			$data['msg'] = 'ok';
		}
		while(0);

		return $this->jsonResponse($data, (empty($data['error']) ? 200 : 400));

		// -------------------------------------------------------------------------------------------------------------------------------
		// TODO: Obtener los roles del usuario (permisos)
		/*
		$data_user_roles = UtilData::get_user_roles($this->app(), $user_data['user_id']);
		$user_roles = array();

		foreach ($data_user_roles as $rol)
		{
			$user_roles[] = $rol['value'];
		}

		$this->setSessionData('user.rol',      $user_roles);
		$this->setSessionData('user_real.rol', $user_roles);

		$data_user_menu = UtilData::get_user_menu_roles($this->app(), $user_data['user_id']);
		$user_menu = array();

		foreach ($data_user_menu as $menu)
		{
			$user_menu[] = $menu['value'];
		}

		$this->setSessionData('user.menu', $user_menu);
		*/
		// -------------------------------------------------------------------------------------------------------------------------------
	}

	function password_change()
	{
		$data = array();

		$old_password = trim($this->requestData('POST', 'old_password', NULL));
		$password = trim($this->requestData('POST', 'password', NULL));

		do
		{
			if(! $this->auth()->isAuthenticated())
			{
				$data['error'] = 'Debes estar autentificado para realizar esta accion';
				break;
			}

			if(! $old_password || ! $password)
			{
				$data['error'] = 'Datos insuficientes';
				break;
			}

			if(! $this->validator()->isAlphaNumeric($old_password, false, false) || strlen($old_password) != 64
				|| ! $this->validator()->isAlphaNumeric($password, false, false) || strlen($password) != 64)
			{
				$data['error'] = 'Debes especificar una contraseña valida';
				break;
			}

			$user_id = $this->getSessionData('user_real.id');

			$old_password .= $this->app()['security.salt'];
			$old_password = hash('sha256', $old_password);

			$sql = "SELECT user_id from user
				where user_id = $user_id
					and password = '$old_password'";

			if($this->db()->hasNotResults($sql))
			{
				$data['error'] = 'Contraseña anterior incorrecta';
				break;
			}

			$password .= $this->app()['security.salt'];
			$password = hash('sha256', $password);

			$sql = "UPDATE user set password = '$password' where user_id = $user_id";
			$this->db()->run($sql);

			$data['msg'] = 'ok';
		}
		while(0);

		return $this->jsonResponse($data, (empty($data['error']) ? 200 : 400));
	}

	function password_change_token($token)
	{
		$data = array();

		$password = trim($this->requestData('POST', 'password', NULL));

		do
		{
			$token = $this->db()->toSQL($token, false);

			$sql = "SELECT token_id, user_id, active
				from token
				where token_type_id = (select token_type_id from token_type where name = 'user_password_change')
					and date_expire > NOW()
					and token = $token";
			$row = $this->db()->getFirstRow($sql);

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

			if(! $this->validator()->isAlphaNumeric($password, false, false) || strlen($password) != 64)
			{
				$data['error'] = 'Debes especificar una contraseña valida';
				break;
			}

			$password .= $this->app()['security.salt'];
			$password = hash('sha256', $password);

			$sql = "UPDATE user set active = 1, password = '$password' where user_id = " . $row['user_id'];
			$this->db()->run($sql);

			$sql = "UPDATE token set active = 0 where token_id = " . $row['token_id'];
			$this->db()->run($sql);

			$data['msg'] = 'ok';
		}
		while(0);

		return $this->jsonResponse($data, (empty($data['error']) ? 200 : 400));
	}

	function password_change_token_check($token)
	{
		$data = array();

		do
		{
			$token = $this->db()->toSQL($token, false);

			$sql = "SELECT token_id, user_id, active
				from token
				where token_type_id = (select token_type_id from token_type where name = 'user_password_change')
					and date_expire > NOW()
					and token = $token";
			$row = $this->db()->getFirstRow($sql);

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

		return $this->jsonResponse($data, (empty($data['error']) ? 200 : 400));
	}

	function password_forgot()
	{
		$data = array();

		$username = trim($this->requestData('POST', 'username', NULL));

		do
		{
			if(! $this->validator()->isAlphaNumeric($username, true))
			{
				$data['error'] = 'Usuario no valido';
			}

			$sql = "SELECT user_id
				from user
				where email = '$username' or username = '$username'";
			$user_id = $this->db()->getValue($sql);

			if(! $user_id)
			{
				$data['error'] = 'Usuario no encontrado';
				break;
			}

			$sql = "SELECT token_type_id from token_type where name = 'user_password_change'";
			$token_type_id = $this->db()->getValue($sql);

			$token = $this->auth()->generateToken();

			$sql = "INSERT into token (token, user_id, token_type_id, date_expire, active)
				values ('$token', $user_id, $token_type_id, FROM_UNIXTIME(" . strtotime("+1 week") . "), 1)";
			$token_id = $this->db()->runInsert($sql);

			$user_email = $this->db()->getValue("SELECT email from user where user_id = $user_id");
			$password_change_path = $this->generateUrl('rt_usr_password_change_token', array('token' => $token));

			$email_dev = $this->app()['email.sender'];

			try
			{
				$this->email()->setSubject("Cambiar contraseña");
				$this->email()->setFrom(array($email_dev));
				$this->email()->setSender(array($email_dev));
				$this->email()->setReplyTo(array($email_dev));
				$this->email()->setReturnPath($email_dev);
				$this->email()->setTo(array($user_email));
				$this->email()->setBody("<!DOCTYPE html><html><body><pre>Cambiar contraseña:\n\n$password_change_path</pre></body></html>");
				$this->email()->send();
			}
			catch(\Exception $e)
			{
				$data['error'] = 'Ha ocurrido un error al enviar el email de cambio de contraseña';
				break;
			}

			$data['msg'] = 'ok';
		}
		while(0);

		return $this->jsonResponse($data, (empty($data['error']) ? 200 : 400));
	}

	function logout()
	{
		$user_id = $this->getSessionData('user_real.id');
		$user_username = $this->getSessionData('user_real.username');

		if($user_id)
		{
			$this->log()->addNotice("Sesión cerrada  -> Usuario: $user_username (ID: $user_id)");
		}

		$this->session()->clear(); // Redundante, pero por si acaso...
		$this->session()->invalidate();

		return $this->jsonResponse(array('msg' => 'ok'), 200);
	}
}
