<?php

namespace Src\Frontend\User;

use Silex\Application;
use Silex\ControllerProviderInterface;

class UserProvider implements ControllerProviderInterface
{
	public function connect(Application $app)
	{
		$user = $app['controllers_factory'];

		$user->get('/register/', 'Src\\Frontend\\User\\User::register')
		->bind('rt_usr_register');
		$user->get('/active/{token}/', 'Src\\Frontend\\User\\User::active')
		->bind('rt_usr_active')->assert('token', '^[a-zA-Z0-9]{64}$');
		$user->match('/login/', 'Src\\Frontend\\User\\User::login')
		->bind('rt_usr_login')->method('GET|POST');
		$user->get('/password/change/', 'Src\\Frontend\\User\\User::password_change')
		->bind('rt_usr_password_change');
		$user->get('/password/change/{token}/', 'Src\\Frontend\\User\\User::password_change_token')
		->bind('rt_usr_password_change_token')->assert('token', '^[a-zA-Z0-9]{64}$');
		$user->get('/password/forgot/', 'Src\\Frontend\\User\\User::password_forgot')
		->bind('rt_usr_password_forgot');
		$user->get('/logout/', 'Src\\Frontend\\User\\User::logout')
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
