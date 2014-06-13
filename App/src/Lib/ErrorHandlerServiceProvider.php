<?php

namespace Src\Lib;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception;

class ErrorHandlerServiceProvider implements ServiceProviderInterface
{
	function register(Application $app)
	{
		$app->error(function (\Exception $exception, $code) use ($app)
		{
			if(! function_exists('to_HTML'))
			{
				function to_HTML($string)
				{
					return str_replace("\t", '&#09;', str_replace("\n", '<br>', htmlspecialchars($string)));
				}
			}

			if(! function_exists('to_SQL'))
			{
				function to_SQL($string)
				{
					return str_replace('&#09;', "\t", str_replace('<br>', "\n", str_replace(array("'", '"'), '', str_replace('\\', '\\\\', $string))));
				}
			}

			@session_start();
			$e_app_id = 'Silex v' . Application::VERSION;
			$e_date = date('d/m/Y H:i:s');
			$e_environment = ($app['debug'] ? 'dev' : 'prod');
			$e_route = $exception->getFile();
			$e_line = $exception->getLine();
			$e_code = $exception->getCode();
			$e_error_msg = ($exception->getMessage() ?: 'null');
			$e_trace = $exception->getTraceAsString();
			$e_backtrace = print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), true);
			$e_get_var_dump = print_r($_GET, true);
			$e_post_var_dump = print_r($_POST, true);
			$e_session_var_dump = print_r($_SESSION, true);
			$e_session_user_id = @($_SESSION['_sf2_attributes']['user_real.id'] ?: null);
			$e_session_username = @($_SESSION['_sf2_attributes']['user_real.usuario'] ?: 'Anonymous');
			$e_cookie_var_dump = print_r($_COOKIE, true);
			$e_server_var_dump = print_r($_SERVER, true);
			$e_server_method = @$_SERVER['REQUEST_METHOD'];
			$e_server_client_ip = @$_SERVER['REMOTE_ADDR'];
			$e_server_useragent = @$_SERVER['HTTP_USER_AGENT'];
			@session_write_close();

			//--------------------------------------------------------------------

			$exception_msg = '';
			$exception_msg .= '<b>APP</b>: ' . to_HTML($e_app_id) . ' (<b>ENTORNO</b>: ' . to_HTML($e_environment) . ')<br>';
			$exception_msg .= '<b>USUARIO</b>: ' . to_HTML($e_session_username) . ' (<b>IP</b>: ' . to_HTML($e_server_client_ip) . ')<br>';
			$exception_msg .= '<b>USERAGENT</b>: ' . to_HTML($e_server_useragent) . '<br>';
			$exception_msg .= '<b>FECHA</b>: ' . to_HTML($e_date) . '<br>';
			$exception_msg .= '<b>MÉTODO</b>: ' . to_HTML($e_server_method) . '<br>';
			$exception_msg .= '<b>RUTA</b>: ' . to_HTML($e_route) . '<br>';
			$exception_msg .= '<b>LÍNEA</b>: ' . to_HTML($e_line) . '<br>';
			$exception_msg .= '<b>CÓDIGO</b>: ' . to_HTML($e_code) . '<br>';
			$exception_msg .= '<b>ERROR</b>: ' . to_HTML($e_error_msg) . '<br>';
			$exception_msg .= '<br>';
			$exception_msg .= '<b>TRAZA</b>:<br>&nbsp;<br>' .            to_HTML($e_trace) . '<br>&nbsp;<br>';
			$exception_msg .= '<b>GET_VAR_DUMP</b>:<br>&nbsp;<br>' .     to_HTML($e_get_var_dump) . '<br>&nbsp;<br>';
			$exception_msg .= '<b>POST_VAR_DUMP</b>:<br>&nbsp;<br>' .    to_HTML($e_post_var_dump) . '<br>&nbsp;<br>';
			$exception_msg .= '<b>SESSION_VAR_DUMP</b>:<br>&nbsp;<br>' . to_HTML($e_session_var_dump) . '<br>&nbsp;<br>';
			$exception_msg .= '<b>COOKIE_VAR_DUMP</b>:<br>&nbsp;<br>' .  to_HTML($e_cookie_var_dump) . '<br>&nbsp;<br>';
			$exception_msg .= '<b>SERVER_VAR_DUMP</b>:<br>&nbsp;<br>' .  to_HTML($e_server_var_dump) . '<br>&nbsp;<br>';
			$exception_msg .= '<b>BACKTRACE</b>:<br>&nbsp;<br>' .        to_HTML($e_backtrace) . '<br>&nbsp;<br>';
			$exception_msg .= '<br>------------------------<br>&nbsp;';
			$exception_msg .= '<br>' . to_HTML($e_app_id) . ' (' . to_HTML($e_date) . ')';

			try
			{
				$app['sql']->connect();

				if($app['sql']->isConnected())
				{
					$sql = "INSERT INTO log_error
						(
							app_name,
							date,
							environment,
							checked,
							user_id,
							ip,
							method,
							path,
							line,
							error_code,
							error_message,
							trace,
							backtrace,
							get_var_dump,
							post_var_dump,
							session_var_dump,
							cookie_var_dump,
							server_var_dump
						)
						VALUES
						(
							'$e_app_id',
							FROM_UNIXTIME(" . strtotime(str_replace('/', '.', $e_date)) . "),
							'$e_environment',
							0,
							" . ($e_session_user_id ?: 'null') . ",
							'$e_server_client_ip',
							'$e_server_method',
							'" . substr(to_SQL($e_route), 0, 255) . "',
							$e_line,
							$e_code,
							'" . substr(to_SQL($e_error_msg), 0, 1024) . "',
							'" . to_SQL($e_trace) . "',
							'" . to_SQL($e_backtrace) . "',
							'" . to_SQL($e_get_var_dump) . "',
							'" . to_SQL($e_post_var_dump) . "',
							'" . to_SQL($e_session_var_dump) . "',
							'" . to_SQL($e_cookie_var_dump) . "',
							'" . to_SQL($e_server_var_dump) . "'
						)";

					// Intenta guardar el error en base de datos
					$app['sql']->run($sql);
				}
			}
			catch(\Exception $e) {}

			if ($app['debug'])
			{
				return;
				// DISABLED because "WhoopsServiceProvider" will be handling errors in dev environment instead this
				// return new Response($app['util']->wrap($exception_msg));
			}

			try
			{
				$email_dev = $app['email.sender'];

				// Intenta enviar un email con el error
				$app['email']->setSubject("[ERROR] $e_app_id ($e_environment) - Reporte del Error ($e_date)");
				$app['email']->setFrom(array($email_dev));
				$app['email']->setSender(array($email_dev));
				$app['email']->setReplyTo(array($email_dev));
				$app['email']->setReturnPath($email_dev);
				$app['email']->setTo(array($email_dev));
				$app['email']->setBody("<!DOCTYPE html><html><body><pre>$exception_msg<br /></pre></body></html>");
				$app['email']->send();
			}
			catch(\Exception $e) {}

			return new Response($app['twig']->render('error.twig'), 400);
		});
	}

	function boot(Application $app) {}
}
