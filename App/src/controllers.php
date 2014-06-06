<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// Control de errores
require_once __DIR__.'/error_handler.php';

// Controladores
require_once __DIR__.'/Backend/mount.php';
require_once __DIR__.'/Frontend/mount.php';

// Ruta por defecto
$app->match('{fallback_url}', function($fallback_url) use ($app)
{
	$app['monolog']->addError('Ruta no encontrada: "' . rawurlencode($fallback_url) . '"', array(
		'session.user_real.id' => $app['session']->get('user_real.id', NULL),
		'session.user_real.usuario' => $app['session']->get('user_real.usuario', NULL)
	));

	return new Response($app['twig']->render('error.twig', array('mensaje' => 'Error 404 - Página no encontrada')), 404);
})
->bind('rt_error_not_found')->assert('fallback_url', '^(?!_profiler/)(.+)');

$app->before(function (Request $request) use ($app)
{
	// Si el servidor está en mantenimiento, mostramos un mensaje de información (NO ejecutamos ningun controlador)
	if($app['maintenance'])
	{
		if(! in_array($app['url_matcher']->matchRequest($app['request'])['_route'], array('_wdt','_profiler')))
		{
			if($request->getMethod() == "GET")
			{
				return new Response($app['twig']->render('info.twig', array('mensaje' => 'Aplicación en mantenimiento, por favor regresa en unos minutos')), 503);
			}
			else
			{
				return $app->json(array('error' => 'Aplicación en mantenimiento, por favor vuelve a intentarlo en unos minutos'), 503);
			}
		}
	}

	// Re-identificamos al usuario en caso de que la aplicación requiera de una version distinta de variables de sesión
	if($app['auth']->isAuthenticated() && $app['session']->get('session.version') != $app['session.version'])
	{
		if($request->getMethod() != "GET")
		{
			return $app->json(array('error' => 'Sesion caducada/invalida, por favor refresca la pagina'), 400);
		}

		return new Response($app['twig']->render('user/login_redirect.twig',
			array('redirect_path' => $app->escape($request->getRequestUri()))));
	}

	// Convierte el json de la petición en un array PHP
	if (0 === strpos($request->headers->get('Content-Type'), 'application/json'))
	{
		$data = json_decode($request->getContent(), true);
		$request->request->replace(is_array($data) ? $data : array());
	}
});

$app->after(function (Request $request, Response $response)
{
	// Añadimos cabeceras para evitar que el navegador cree caché
	$response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0, private');
	$response->headers->set('Pragma', 'no-cache');
	$response->headers->set('Expires', 'Sat, 01 Jul 2000 12:34:56 GMT');
});
