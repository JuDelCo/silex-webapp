<?php

namespace Src\Frontend\User;

use Src\Lib\ControllerBase;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class User extends ControllerBase
{
	function register()
	{
		if($this->auth()->isAuthenticated())
		{
			return $this->app()->redirect($this->app()['url_generator']->generate('rt_root'));
		}

		return new Response($this->twig()->render('user/register.twig'));
	}

	function active($token)
	{
		$subRequest = Request::create($this->app()['url_generator']->generate('rta_usr_active', array('token' => $token)), 'POST');
		$response = $this->app()->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

		$data = json_decode($response->getContent(), true);

		if(isset($data['error']))
		{
			return new Response($this->twig()->render('error.twig', array('mensaje' => $data['error'])));
		}

		return new Response($this->twig()->render('info.twig', array('mensaje' => 'Usuario activado con éxito. Ya puedes iniciar sesión.')));
	}

	function login()
	{
		if($this->auth()->isAuthenticated())
		{
			return $this->app()->redirect($this->app()['url_generator']->generate('rt_main'));
		}

		return new Response($this->twig()->render('user/login.twig', array('redirect_path' => $this->request()->request->get('redirect_path'))));
	}

	function password_change()
	{
		if(! $this->auth()->isAuthenticated())
		{
			return $this->app()->redirect($this->app()['url_generator']->generate('rt_root'));
		}

		return new Response($this->twig()->render('user/password_change.twig'));
	}

	function password_change_token($token)
	{
		$subRequest = Request::create($this->app()['url_generator']->generate('rta_usr_password_change_token_check', array('token' => $token)), 'POST');
		$response = $this->app()->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

		$data = json_decode($response->getContent(), true);

		if(isset($data['error']))
		{
			return new Response($this->twig()->render('error.twig', array('mensaje' => $data['error'])));
		}

		return new Response($this->twig()->render('user/password_change_token.twig', array('password_change_token' => $token)));
	}

	function password_forgot()
	{
		if($this->auth()->isAuthenticated())
		{
			return $this->app()->redirect($this->app()['url_generator']->generate('rt_root'));
		}

		return new Response($this->twig()->render('user/password_forgot.twig'));
	}

	function logout()
	{
		if(! $this->auth()->isAuthenticated())
		{
			return $this->app()->redirect($this->app()['url_generator']->generate('rt_root'));
		}

		$subRequest = Request::create($this->app()['url_generator']->generate('rta_usr_logout'), 'POST');
		$this->app()->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

		return new Response($this->twig()->render('info.twig', array('mensaje' => 'Sesión cerrada')));
	}
}
