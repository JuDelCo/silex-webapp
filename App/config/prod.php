<?php

use Silex\Provider\MonologServiceProvider;
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DBALException;

require_once __DIR__.'/../../../ServerConf.php';

$app['maintenance'] = $server_conf['maintenance'];
$app['session.version'] = '1.0.0';
$app['security.salt'] = $security_hash['salt'];
$app['security.login_token'] = $security_hash['login'];
$app['excel.disable_cache'] = false;
$app['email.sender'] = $mail_conf['main']['sender'];
$app['email.debug'] = $mail_conf['main']['debug'];

$app['path.info'] = array(
	'root'        => $path_info['root'],
	'chunks'      => $path_info['chunks'],
	'temp'        => $path_info['temp'],
	'excel'       => $path_info['excel']
);

$app['db.options'] = array(
	'driver'   => $db_conf['main']['driver'],
	'host'     => $db_conf['main']['host'],
	'dbname'   => $db_conf['main']['database'],
	'user'     => $db_conf['main']['username'],
	'password' => $db_conf['main']['password']
);

$app['swiftmailer.options'] = array(
	'host'       => $mail_conf['main']['host'],
	'port'       => $mail_conf['main']['port'],
	'username'   => $mail_conf['main']['username'],
	'password'   => $mail_conf['main']['password'],
	'encryption' => 'ssl',
	'auth_mode'  => 'login'
);

unset($db_conf, $mail_conf, $security_hash, $path_info, $server_conf);

// ----------------------------------------------

// TranslationServiceProvider
$app['locale'] = 'es';
$app['locale_fallback'] = 'es';
$app['session.default_locale'] = 'es';

// TwigServiceProvider
$app['twig.path'] = array(__DIR__.'/../templates');
$app['twig.options'] = array('cache' => __DIR__.'/../var/cache/twig');

// MonologServiceProvider
$app->register(new MonologServiceProvider(), array(
	'monolog.logfile' => __DIR__.'/../var/logs/app_prod.log',
	'monolog.level'   => Logger::NOTICE,
	'monolog.name'    => 'App',
));
$monolog_formatter = new LineFormatter("[%datetime%] (%channel%) %level_name%: %message% %context%\n", 'Y-m-d H:i:s');
$monolog_stream_prod = $app['monolog']->popHandler();
$monolog_stream_prod->setFormatter($monolog_formatter);
$app['monolog']->pushHandler($monolog_stream_prod);

$logger = new DebugStack();
$app->extend('db.config', function(Configuration $configuration) use ($logger)
{
	$configuration->setSQLLogger($logger);

	return $configuration;
});

// Escribimos la última SQL errónea ejecutada en el Log (Si hubiera errores)
$app->error(function(\Exception $e, $code) use ($app, $logger)
{
	if(count($logger->queries) && ($e instanceof \PDOException || $e instanceof DBALException))
	{
		$query = array_pop($logger->queries); // Obtenemos la última SQL

		$app['monolog']->addError('SQL Query', array(
			'session.user_real.id' => ($app['session']->isStarted() ? $app['session']->get('user_real.id', 'null') : 'session_closed'), 
			'session.user_real.usuario' => ($app['session']->isStarted() ? $app['session']->get('user_real.usuario', 'null') : 'session_closed'),
			'query.ms' => $query['executionMS'],
			'query.sql' => $query['sql'],
			'query.params' => $query['params'],
			'query.types' => $query['types']
		));
	}
});
