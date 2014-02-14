<?php

require_once __DIR__.'/user_controller.php';

$user_controller = $app['controllers_factory'];

$user_controller->post('/register/', 'ApiUserController::register')
->bind('rta_usr_register');
$user_controller->post('/active/{token}/', 'ApiUserController::active')
->bind('rta_usr_active')->assert('token', '^[a-zA-Z0-9]{64}$');
$user_controller->post('/login/', 'ApiUserController::login')
->bind('rta_usr_login');
$user_controller->post('/password/change/', 'ApiUserController::password_change')
->bind('rta_usr_password_change');
$user_controller->post('/password/change/{token}/', 'ApiUserController::password_change_token')
->bind('rta_usr_password_change_token')->assert('token', '^[a-zA-Z0-9]{64}$');
$user_controller->post('/password/change/{token}/check/', 'ApiUserController::password_change_token_check')
->bind('rta_usr_password_change_token_check')->assert('token', '^[a-zA-Z0-9]{64}$');
$user_controller->post('/password/forgot/', 'ApiUserController::password_forgot')
->bind('rta_usr_password_forgot');
$user_controller->post('/logout/', 'ApiUserController::logout')
->bind('rta_usr_logout');

return $user_controller;
