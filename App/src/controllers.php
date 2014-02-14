<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

// Control de errores
require_once __DIR__.'/error_handler.php';

// Controladores
require_once __DIR__.'/backend/mount.php';
require_once __DIR__.'/frontend/mount.php';

// Ruta por defecto
$app->match('{fallback_url}', function($fallback_url) use ($app)
{
	$app['monolog']->addError('Ruta no encontrada: "' . rawurlencode($fallback_url) . '"');	
	
	return new Response($app['twig']->render('error.twig', array('mensaje' => 'Error 404 - Página no encontrada')), 404);
})
->bind('rt_error_not_found')->assert('fallback_url', '^(?!_profiler/)(.+)');

$app->before(function (Request $request) use ($app)
{
	$matched_route = $app['url_matcher']->matchRequest($request)['_route'];

	// Afecta a todas las rutas a excepción de la pagina principal, las de identificación, la del web profiler o la de error 404
	if(! in_array($matched_route, array(
			'rt_root','rt_index',
			'rt_usr_login','rta_usr_login',
			'rt_usr_logout','rta_usr_logout',
			'rt_usr_register','rta_usr_register','rt_usr_active',
			'rt_usr_password_forgot','rta_usr_password_forgot','rt_usr_password_change_token','rta_usr_password_change_token',
			'rt_error_not_found'
		))
		and preg_match('/^(.+)_profiler\/(.+)$/i', $request->getPathInfo()) === 0)
	{
		// Identificamos al usuario (En caso de que no lo esté o la aplicación requiera de una version distinta de variables de sesión)
		if(! $app['auth']->isAuthenticated() || $app['session']->get('session.version') != $app['session.version'])
		{
			if($request->getMethod() != "GET")
			{
				return $app->json(array('error' => 'Sesion caducada/invalida, por favor refresca la pagina'), 400);
			}

			return new Response($app['twig']->render('user/login_redirect.twig', array('redirect_path' => $app->escape($request->getRequestUri()))));
		}
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
