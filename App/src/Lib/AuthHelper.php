<?php

namespace Src\Lib;

use Src\Lib\Application;

class AuthHelper extends Application
{
	function generateToken()
	{
		return hash('sha256', base_convert(sha1(uniqid(mt_rand() . $this->app('security.salt'), true)), 16, 36));
	}

	function generateUniqueToken()
	{
		$existe = true;

		do
		{
			$token = $this->generateToken();

			$sql = "SELECT token_id from token where token = '$token'";
			$existe = $this->db()->hasResults($sql);
		}
		while($existe);

		return $token;
	}

	function isAuthenticated()
	{
		$user_id = $this->getSessionData('user_real.id');

		if(! empty($user_id))
		{
			return true;
		}

		return false;
	}

	function isAuthorised($roles)
	{
		if(! $this->isAuthenticated())
		{
			return false;
		}

		$user_roles = $this->getSessionData('user.rol', array()); // user_real

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

	function firewall($role_controlled_routes, $anonymous_routes = array())
	{
		$matched_route = $this->app('url_matcher')->matchRequest($this->request())['_route'];

		// Autentificación (rutas que se puedan visitar sin estar identificado)
		if(in_array($matched_route, $anonymous_routes))
		{
			return null;
		}

		// La ruta no puede ser visitada anónimamente (se ha revisado el array anterior)
		if(! $this->isAuthenticated())
		{
			if($this->request()->getMethod() != "GET")
			{
				return $this->jsonResponse(array('error' => 'Debes identificarte primero para poder realizar esa accion'), 400);
			}

			return $this->renderResponse('user/login_redirect.twig',
				array('redirect_path' => $this->app()->escape($this->request()->getRequestUri())));
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
			if($this->request()->getMethod() != "GET")
			{
				return $this->jsonResponse(array('error' => 'No tienes permisos suficientes para realizar esa accion'), 400);
			}

			return $this->renderResponse('error.twig', array('mensaje' => 'Error - No tienes permisos suficientes para acceder'), 404);
		}

		return null;
	}
}
