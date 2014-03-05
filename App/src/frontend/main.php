<?php

use Symfony\Component\HttpFoundation\Response;

$main_controller = $app['controllers_factory'];

$main_controller->get('/', function () use ($app)
{
	return new Response($app['twig']->render('info.twig', 
		array('mensaje' => 'Página principal', 'menu_alt_info_label' => 'Menú principal', 'titulo' => 'Página Principal')));
})
->bind('rt_root');

$main_controller->get('/index.{extension}', function () use ($app)
{
	return $app->redirect($app['url_generator']->generate('rt_root'));
})
->bind('rt_index')->assert('extension', '^(php)|(html)$');

$main_controller->get('/inicio/', function () use ($app)
{
	return new Response($app['twig']->render('info.twig', 
		array('mensaje' => 'Página principal (posterior al login)', 'menu_alt_info_label' => 'Menú principal', 'titulo' => 'Página Principal')));
})
->bind('rt_main');

$main_controller->before(function () use ($app)
{
	$anonymous_routes = array('rt_root','rt_index');
	$role_controlled_routes = array(
		array(
			'routes' => array('rt_main')
		)
	);

	return $app['auth']->firewall($role_controlled_routes, $anonymous_routes);
});

return $main_controller;
