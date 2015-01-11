<?php

// ---------------------------------------

$db_conf = array();
$mail_conf = array();
$security_hash = array();
$path_info = array();
$server_conf = array();

// ---------------------------------------

$db_conf['main'] = array(
	'driver'        => 'pdo_mysql',
	'host'          => '127.0.0.1',
	'port'          => '3306',
	'username'      => 'username',
	'password'      => 'password',
	'database'      => 'database'
);

// ---------------------------------------

$mail_conf['main'] = array(
	'mailer'        => 'smtp',
	'host'          => 'smtp.gmail.com',
	'port'          => 465,
	'SMTPAuth'      => true,
	'username'      => 'username@gmail.com',
	'password'      => 'password',
	'pluginDir'     => '',
	'timeout'       => 60,
	'sender'        => 'username@gmail.com',
	'debug'         => 'username@gmail.com'
);

$mail_conf['alt'] = array(
	'mailer'        => 'smtp',
	'host'          => '127.0.0.1',
	'port'          => 45540,
	'SMTPAuth'      => true,
	'username'      => 'admin@example.com',
	'password'      => 'demo',
	'pluginDir'     => '',
	'timeout'       => 60,
	'sender'        => '',
	'debug'         => ''
);

// ---------------------------------------

// base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
$security_hash['salt'] = 'SALT';
$security_hash['login'] = md5('SALT' . date('d/m/Y'));

// ---------------------------------------

$path_info['root'] = 'C:/Data/';
$path_info['chunks'] = 'temp/chunks/';
$path_info['temp'] = 'temp/';
$path_info['excel'] = 'Excel/';

// ---------------------------------------

$server_conf['maintenance'] = false;
$server_conf['maintenance_msg'] = 'Aplicación en mantenimiento, por favor espera unos minutos';
// Aplicación en mantenimiento, por favor vuelve a intentarlo en unos minutos
// Aplicación en mantenimiento, por favor espera unos minutos

// ---------------------------------------
