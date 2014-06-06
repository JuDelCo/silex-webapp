<?php

use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\WebProfilerServiceProvider;
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;

require __DIR__.'/prod.php';

$app['debug'] = true;

$monolog_formatter = new LineFormatter("[%datetime%] (%channel%) %level_name%: %message% %context%\n", 'Y-m-d H:i:s');
$monolog_stream_dev = new StreamHandler(__DIR__ . '/../var/logs/app_dev.log', Logger::NOTICE);
$monolog_stream_dev->setFormatter($monolog_formatter);

// Sustituimos el LogHandler de Producción (SOLO guardamos Logs en app_dev.log si estamos en entorno de desarrollo)
$app['monolog']->popHandler();
$app['monolog']->pushHandler($monolog_stream_dev);

if (isset($app['monolog.handler.debug']))
{
	$app['monolog']->pushHandler($app['monolog.handler.debug']);
}

$app->register(new ServiceControllerServiceProvider());
$app->register($p = new WebProfilerServiceProvider(), array(
    'profiler.cache_dir' => __DIR__.'/../var/cache/profiler',
));

// Escribimos todas las consultas SQL ejecutadas en el log
$app->finish(function() use ($app, $logger)
{
	foreach ($logger->queries as $query)
	{
		$app['monolog']->addNotice('SQL Query', array(
			'session.user_real.id' => ($app['session']->isStarted() ? $app['session']->get('user_real.id', 'null') : 'session_closed'),
			'session.user_real.usuario' => ($app['session']->isStarted() ? $app['session']->get('user_real.usuario', 'null') : 'session_closed'),
			'query.ms' => $query['executionMS'],
			'query.sql' => $query['sql'],
			'query.params' => $query['params'],
			'query.types' => $query['types']
		));
	}
});
