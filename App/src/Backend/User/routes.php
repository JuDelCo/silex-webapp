<?php

require_once __DIR__.'/user.php';

$user = $app['controllers_factory'];

$user->post('/register/', 'ApiUser::register')
->bind('rta_usr_register');
$user->post('/active/{token}/', 'ApiUser::active')
->bind('rta_usr_active')->assert('token', '^[a-zA-Z0-9]{64}$');
$user->post('/login/', 'ApiUser::login')
->bind('rta_usr_login');
$user->post('/password/change/', 'ApiUser::password_change')
->bind('rta_usr_password_change');
$user->post('/password/change/{token}/', 'ApiUser::password_change_token')
->bind('rta_usr_password_change_token')->assert('token', '^[a-zA-Z0-9]{64}$');
$user->post('/password/change/{token}/check/', 'ApiUser::password_change_token_check')
->bind('rta_usr_password_change_token_check')->assert('token', '^[a-zA-Z0-9]{64}$');
$user->post('/password/forgot/', 'ApiUser::password_forgot')
->bind('rta_usr_password_forgot');
$user->post('/logout/', 'ApiUser::logout')
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