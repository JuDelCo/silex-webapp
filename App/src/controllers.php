<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

// Ruta por defecto
$app->match('{fallback_url}', function($fallback_url) use ($app)
{
	$app['monolog']->addError('Ruta no encontrada: "' . rawurlencode($fallback_url) . '"');	
	
	return new Response('Pagina no encontrada', 404);
})
->bind('rt_error_not_found')->assert('fallback_url', '^(?!_profiler/)(.+)');

$app->before(function (Request $request) use ($app)
{
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
