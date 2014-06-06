<?php

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

class AuthHelperUtil
{
	protected $app;

	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	public function generateToken()
	{
		return hash('sha256', base_convert(sha1(uniqid(mt_rand() . $this->app['security.salt'], true)), 16, 36));
	}

	public function generateUniqueToken()
	{
		$existe = true;

		do
		{
			$token = $this->generateToken();

			$sql = "SELECT token_id from token where token = '$token'";
			$existe = $this->app['sql']->hasResults($sql);
		}
		while($existe);

		return $token;
	}

	public function isAuthenticated()
	{
		$user_id = $this->app['session']->get('user_real.id', null);

		if(! empty($user_id))
		{
			return true;
		}

		return false;
	}

	public function isAuthorised($roles)
	{
		if(! $this->isAuthenticated())
		{
			return false;
		}

		$user_roles = $this->app['session']->get('user.rol', array()); // user_real

		if(empty($user_roles))
		{
			return false;
		}

		if(is_array($roles))
		{
			foreach ($roles as $role)
			{
				if(in_array($role, $user_roles))
				{
					return true;
				}
			}
		}
		else
		{
			if(in_array($roles, $user_roles))
			{
				return true;
			}
		}

		return false;
	}

	public function firewall($role_controlled_routes, $anonymous_routes = array())
	{
		$matched_route = $this->app['url_matcher']->matchRequest($this->app['request'])['_route'];

		// Autentificación (rutas que se puedan visitar sin estar identificado)
		if(in_array($matched_route, $anonymous_routes))
		{
			return null;
		}

		// La ruta no puede ser visitada anónimamente (se ha revisado el array anterior)
		if(! $this->isAuthenticated())
		{
			if($this->app['request']->getMethod() != "GET")
			{
				return $this->app->json(array(
					'error' => 'Debes identificarte primero para poder realizar esa accion'), 400);
			}

			return new Response($this->app['twig']->render('user/login_redirect.twig',
				array('redirect_path' => $this->app->escape($this->app['request']->getRequestUri()))));
		}

		$authorized = false;

		// Autorización (rutas que requieren ciertos roles para poder acceder)
		foreach ($role_controlled_routes as $routes_info)
		{
			if(in_array($matched_route, $routes_info['routes']))
			{
				if(! isset($routes_info['roles']) || empty($routes_info['roles'])
					|| $this->isAuthorised($routes_info['roles']))
				{
					$authorized = true;
				}

				break;
			}
		}

		if(! $authorized)
		{
			if($this->app['request']->getMethod() != "GET")
			{
				return $this->app->json(array(
					'error' => 'No tienes permisos suficientes para realizar esa accion'), 400);
			}

			return new Response($this->app['twig']->render('error.twig', array(
				'mensaje' => 'Error - No tienes permisos suficientes para acceder')), 404);
		}

		return null;
	}
}
