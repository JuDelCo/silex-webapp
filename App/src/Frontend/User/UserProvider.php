<?php

namespace Src\Frontend\User;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Src\Frontend\User\User;

class UserProvider implements ControllerProviderInterface
{
	function connect(Application $app)
	{
		$app['controller.util'] = $app->share(function() use ($app)
		{
			return new User($app);
		});

		$user = $app['controllers_factory'];

		$user->get('/register/', 'controller.util:register')
		->bind('rt_usr_register');
		$user->get('/active/{token}/', 'controller.util:active')
		->bind('rt_usr_active')->assert('token', '^[a-zA-Z0-9]{64}$');
		$user->match('/login/', 'controller.util:login')
		->bind('rt_usr_login')->method('GET|POST');
		$user->get('/password/change/', 'controller.util:password_change')
		->bind('rt_usr_password_change');
		$user->get('/password/change/{token}/', 'controller.util:password_change_token')
		->bind('rt_usr_password_change_token')->assert('token', '^[a-zA-Z0-9]{64}$');
		$user->get('/password/forgot/', 'controller.util:password_forgot')
		->bind('rt_usr_password_forgot');
		$user->get('/logout/', 'controller.util:logout')
		->bind('rt_usr_logout');

		$user->before(function () use ($app)
		{
			$anonymous_routes = array('rt_usr_register','rt_usr_active','rt_usr_login',
				'rt_usr_password_change_token','rt_usr_password_forgot','rt_usr_logout');
			$role_controlled_routes = array(
				array(
					'routes' => array('rt_usr_password_change')
				)
			);

			return $app['auth']->firewall($role_controlled_routes, $anonymous_routes);
		});

		return $user;
	}
}
