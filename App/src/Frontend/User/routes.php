<?php

require_once __DIR__.'/user.php';

$user = $app['controllers_factory'];

$user->get('/register/', 'User::register')
->bind('rt_usr_register');
$user->get('/active/{token}/', 'User::active')
->bind('rt_usr_active')->assert('token', '^[a-zA-Z0-9]{64}$');
$user->match('/login/', 'User::login')
->bind('rt_usr_login')->method('GET|POST');
$user->get('/password/change/', 'User::password_change')
->bind('rt_usr_password_change');
$user->get('/password/change/{token}/', 'User::password_change_token')
->bind('rt_usr_password_change_token')->assert('token', '^[a-zA-Z0-9]{64}$');
$user->get('/password/forgot/', 'User::password_forgot')
->bind('rt_usr_password_forgot');
$user->get('/logout/', 'User::logout')
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
