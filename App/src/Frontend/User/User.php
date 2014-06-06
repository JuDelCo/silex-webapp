<?php

namespace Src\Frontend\User;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class User
{
	function register(Application $app)
	{
		if($app['auth']->isAuthenticated())
		{
			return $app->redirect($app['url_generator']->generate('rt_root'));
		}

		return new Response($app['twig']->render('user/register.twig'));
	}

	function active(Application $app, $token)
	{
		$subRequest = Request::create($app['url_generator']->generate('rta_usr_active', array('token' => $token)), 'POST');
		$response = $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

		$data = json_decode($response->getContent(), true);

		if(isset($data['error']))
		{
			return new Response($app['twig']->render('error.twig', array('mensaje' => $data['error'])));
		}

		return new Response($app['twig']->render('info.twig', array('mensaje' => 'Usuario activado con éxito. Ya puedes iniciar sesión.')));
	}

	function login(Application $app)
	{
		if($app['auth']->isAuthenticated())
		{
			return $app->redirect($app['url_generator']->generate('rt_main'));
		}

		return new Response($app['twig']->render('user/login.twig', array('redirect_path' => $app['request']->request->get('redirect_path'))));
	}

	function password_change(Application $app)
	{
		if(! $app['auth']->isAuthenticated())
		{
			return $app->redirect($app['url_generator']->generate('rt_root'));
		}

		return new Response($app['twig']->render('user/password_change.twig'));
	}

	function password_change_token(Application $app, $token)
	{
		$subRequest = Request::create($app['url_generator']->generate('rta_usr_password_change_token_check', array('token' => $token)), 'POST');
		$response = $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

		$data = json_decode($response->getContent(), true);

		if(isset($data['error']))
		{
			return new Response($app['twig']->render('error.twig', array('mensaje' => $data['error'])));
		}

		return new Response($app['twig']->render('user/password_change_token.twig', array('password_change_token' => $token)));
	}

	function password_forgot(Application $app)
	{
		if($app['auth']->isAuthenticated())
		{
			return $app->redirect($app['url_generator']->generate('rt_root'));
		}

		return new Response($app['twig']->render('user/password_forgot.twig'));
	}

	function logout(Application $app)
	{
		if(! $app['auth']->isAuthenticated())
		{
			return $app->redirect($app['url_generator']->generate('rt_root'));
		}

		$subRequest = Request::create($app['url_generator']->generate('rta_usr_logout'), 'POST');
		$app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

		return new Response($app['twig']->render('info.twig', array('mensaje' => 'Sesión cerrada')));
	}
}
