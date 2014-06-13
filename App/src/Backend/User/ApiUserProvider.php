<?php

namespace Src\Backend\User;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Src\Backend\User\ApiUser;

class ApiUserProvider implements ControllerProviderInterface
{
	function connect(Application $app)
	{
		$app['controller.api.user'] = $app->share(function() use ($app)
		{
			return new ApiUser($app);
		});

		$user = $app['controllers_factory'];

		$user->post('/register/', 'controller.api.user:register')
		->bind('rta_usr_register');
		$user->post('/active/{token}/', 'controller.api.user:active')
		->bind('rta_usr_active')->assert('token', '^[a-zA-Z0-9]{64}$');
		$user->post('/login/', 'controller.api.user:login')
		->bind('rta_usr_login');
		$user->post('/password/change/', 'controller.api.user:password_change')
		->bind('rta_usr_password_change');
		$user->post('/password/change/{token}/', 'controller.api.user:password_change_token')
		->bind('rta_usr_password_change_token')->assert('token', '^[a-zA-Z0-9]{64}$');
		$user->post('/password/change/{token}/check/', 'controller.api.user:password_change_token_check')
		->bind('rta_usr_password_change_token_check')->assert('token', '^[a-zA-Z0-9]{64}$');
		$user->post('/password/forgot/', 'controller.api.user:password_forgot')
		->bind('rta_usr_password_forgot');
		$user->post('/logout/', 'controller.api.user:logout')
		->bind('rta_usr_logout');

		$user->before(function () use ($app)
		{
			$anonymous_routes = array('rta_usr_register','rta_usr_active','rta_usr_login','rta_usr_password_change_token',
				'rta_usr_password_change_token_check','rta_usr_password_forgot','rta_usr_logout');
			$role_controlled_routes = array(
				array(
					'routes' => array('rta_usr_password_change')
				)
			);

			return $app['auth']->firewall($role_controlled_routes, $anonymous_routes);
		});

		return $user;
	}
}
