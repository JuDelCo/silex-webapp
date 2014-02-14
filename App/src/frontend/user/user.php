<?php

require_once __DIR__.'/user_controller.php';

$user_controller = $app['controllers_factory'];

$user_controller->get('/register/', 'UserController::register')
->bind('rt_usr_register');
$user_controller->get('/active/{token}/', 'UserController::active')
->bind('rt_usr_active')->assert('token', '^[a-zA-Z0-9]{64}$');
$user_controller->match('/login/', 'UserController::login')
->bind('rt_usr_login')->method('GET|POST');
$user_controller->get('/password/change/', 'UserController::password_change')
->bind('rt_usr_password_change');
$user_controller->get('/password/change/{token}/', 'UserController::password_change_token')
->bind('rt_usr_password_change_token')->assert('token', '^[a-zA-Z0-9]{64}$');
$user_controller->get('/password/forgot/', 'UserController::password_forgot')
->bind('rt_usr_password_forgot');
$user_controller->get('/logout/', 'UserController::logout')
->bind('rt_usr_logout');

return $user_controller;
