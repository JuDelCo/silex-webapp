<?php

namespace Src\Lib;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ControllerBase
{
	protected $app;

	function __construct(Application $app)
	{
		$this->app = $app;
	}

	function app($service = null)
	{
		if(empty($service))
		{
			return $this->app;
		}

		return $this->app[$service];
	}

	function isDebug()
	{
		return $this->app['debug'];
	}

	function stopwatch()
	{
		return $this->app['stopwatch'];
	}

	function request()
	{
		return $this->app['request'];
	}

	function requestData($type, $name, $default = null)
	{
		if(strtoupper($type) == 'ALL')
		{
			return self::request()->get($name, $default);
		}
		else if(strtoupper($type) == 'GET')
		{
			return self::request()->query->get($name, $default);
		}
		else // $_POST fallback
		{
			return self::request()->request->get($name, $default);
		}
	}

	function requestAllData($type)
	{
		if(strtoupper($type) == 'GET')
		{
			return self::request()->query->all();
		}
		else // $_POST fallback
		{
			return self::request()->request->all();
		}
	}

	function session()
	{
		return $this->app['session'];
	}

	function setSessionData($name, $data)
	{
		return self::session()->set($name, $data);
	}

	function getSessionData($name, $default = null)
	{
		return self::session()->get($name, $default);
	}

	function sessionClose()
	{
		return self::session()->save();
	}

	function twig()
	{
		return $this->app['twig'];
	}

	function render($twigTemplate, $parameters = array())
	{
		return self::twig()->render($twigTemplate, $parameters);
	}

	function translator()
	{
		return $this->app['translator'];
	}

	function log()
	{
		return $this->app['monolog'];
	}

	function response($html = '', $status = 200, $headers = array())
	{
		return new Response($html, $status, $headers);
	}

	function jsonResponse($data = array(), $status = 200, $headers = array())
	{
		return $this->app->json($data, $status, $headers);
	}

	function renderResponse($twigTemplate, $parameters = array(), $status = 200, $headers = array())
	{
		return self::response(self::render($twigTemplate, $parameters), $status, $headers);
	}

	function generatePath($routeName, $parameters = array())
	{
		return $this->app['url_generator']->generate($routeName, $parameters, UrlGeneratorInterface::ABSOLUTE_PATH);
	}

	function generateUrl($routeName, $parameters = array())
	{
		return $this->app['url_generator']->generate($routeName, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
	}

	function redirect($url, $status = 302)
	{
		return $this->app->redirect($url, $status);
	}

	function redirectRoute($routeName, $parameters = array(), $status = 302)
	{
		return self::redirect(self::generateUrl($routeName, $parameters, $status));
	}

	function subRequest($url, $parameters = array(), $method = 'POST')
	{
		$subRequest = Request::create($url, $method, $parameters);

		return $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
	}

	function subRequestJson($url, $parameters = array(), $method = 'POST')
	{
		$response = self::subRequest($url, $parameters, $method);

		return json_decode($response->getContent(), true);
	}

	function subRequestRoute($routeName, $routeParameters = array(), $parameters = array(), $method = 'POST')
	{
		return self::subRequest(self::generateUrl($routeName, $routeParameters), $parameters, $method);
	}

	function subRequestRouteJson($routeName, $routeParameters = array(), $parameters = array(), $method = 'POST')
	{
		$response = self::subRequestRoute($routeName, $routeParameters, $parameters, $method);

		return json_decode($response->getContent(), true);
	}

	// ------------------------------------------

	function db()
	{
		return $this->app['sql'];
	}

	function validator()
	{
		return $this->app['validator'];
	}

	function util()
	{
		return $this->app['util'];
	}

	function auth()
	{
		return $this->app['auth'];
	}

	function upload()
	{
		return $this->app['upload'];
	}

	function excel()
	{
		return $this->app['excel'];
	}

	function email()
	{
		return $this->app['email'];
	}
}
