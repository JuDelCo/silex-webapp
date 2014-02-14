<?php

use Symfony\Component\HttpFoundation\Response;

$main_controller = $app['controllers_factory'];

$main_controller->get('/', function () use ($app)
{
	return new Response($app['twig']->render('root.twig'));
})
->bind('rt_root');

$main_controller->get('/index.{extension}', function () use ($app)
{
	return $app->redirect($app['url_generator']->generate('rt_root'));
})
->bind('rt_index')->assert('extension', '^(php)|(html)$');

$main_controller->get('/inicio/', function () use ($app)
{
	return new Response($app['twig']->render('main.twig'));
})
->bind('rt_main');

return $main_controller;
