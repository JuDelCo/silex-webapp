<?php

namespace Src\Lib;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ControllerBase
{
	protected $app;

	function __construct(Application $app)
	{
		$this->app = $app;
	}

	function app()
	{
		return $this->app;
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
		if(strtoupper($type) == 'POST')
		{
			return self::request()->request->get($name, $default);
		}
		else // $_GET by default
		{
			return self::request()->query->get($name, $default);
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

	function getSessionData($name)
	{
		return self::session()->get($name);
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

	function jsonResponse($data = array(), $status = 200, array $headers = array())
	{
		return $this->app->json($data, $status, $headers);
	}

	function renderResponse($twigTemplate, $parameters = array())
	{
		return new Response(self::render($twigTemplate, $parameters));
	}

	function generateUrl($routeName, $parameters = array())
	{
		return $this->app['url_generator']->generate($routeName, $parameters);
	}

	function redirect($url, $status = 302)
	{
		return $this->app->redirect($url, $status);
	}

	function redirectRoute($routeName, $parameters = array(), $status = 302)
	{
		return self::redirect(self::generateUrl($routeName, $parameters, $status));
	}

	function subRequest($url, $method = 'POST')
	{
		$subRequest = Request::create($url, $method);

		return $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
	}

	function subRequestJson($url, $method = 'POST')
	{
		$response = self::subRequest($url, $method);

		return json_decode($response->getContent(), true);
	}

	function subRequestRoute($routeName, $parameters = array(), $method = 'POST')
	{
		return self::subRequest(self::generateUrl($routeName, $parameters), $method);
	}

	function subRequestRouteJson($routeName, $parameters = array(), $method = 'POST')
	{
		$response = self::subRequestRoute($routeName, $parameters, $method);

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
