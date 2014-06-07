<?php

namespace Src\Lib;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ControllerBase
{
	protected $app;

	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	public function app()
	{
		return $this->app;
	}

	public function isDebug()
	{
		return $this->app['debug'];
	}

	public function request()
	{
		return $this->app['request'];
	}

	public function requestData($type, $name, $default = null)
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

	public function session()
	{
		return $this->app['session'];
	}

	public function setSessionData($name, $data)
	{
		return self::session()->set($name, $data);
	}

	public function getSessionData($name)
	{
		return self::session()->get($name);
	}

	public function twig()
	{
		return $this->app['twig'];
	}

	public function render($twigTemplate, $parameters = array())
	{
		return self::twig()->render($twigTemplate, $parameters);
	}

	public function translator()
	{
		return $this->app['translator'];
	}

	public function log()
	{
		return $this->app['monolog'];
	}

	public function jsonResponse($data = array(), $status = 200, array $headers = array())
	{
		return $this->app->json($data, $status, $headers);
	}

	public function renderResponse($twigTemplate, $parameters = array())
	{
		return new Response(self::render($twigTemplate, $parameters));
	}

	public function generateUrl($routeName, $parameters = array())
	{
		return $this->app['url_generator']->generate($routeName, $parameters);
	}

	public function redirect($url, $status = 302)
	{
		return $this->app->redirect($url, $status);
	}

	public function redirectRoute($routeName, $parameters = array(), $status = 302)
	{
		return self::redirect(self::generateUrl($routeName, $parameters, $status));
	}

	public function subRequest($url, $method = 'POST')
	{
		$subRequest = Request::create($url, $method);

		return $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
	}

	public function subRequestJson($url, $method = 'POST')
	{
		$response = self::subRequest($url, $method);

		return json_decode($response->getContent(), true);
	}

	public function subRequestRoute($routeName, $parameters = array(), $method = 'POST')
	{
		return self::subRequest(self::generateUrl($routeName, $parameters), $method);
	}

	public function subRequestRouteJson($routeName, $parameters = array(), $method = 'POST')
	{
		$response = self::subRequestRoute($routeName, $parameters, $method);

		return json_decode($response->getContent(), true);
	}

	// ------------------------------------------

	public function db()
	{
		return $this->app['sql'];
	}

	public function validator()
	{
		return $this->app['validator'];
	}

	public function util()
	{
		return $this->app['util'];
	}

	public function auth()
	{
		return $this->app['auth'];
	}

	public function upload()
	{
		return $this->app['upload'];
	}

	public function excel()
	{
		return $this->app['excel'];
	}

	public function email()
	{
		return $this->app['email'];
	}
}
