<?php

namespace Src\Lib;

use Silex\Application;

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

	public function request()
	{
		return $this->app['request'];
	}

	public function session()
	{
		return $this->app['session'];
	}

	public function twig()
	{
		return $this->app['twig'];
	}

	public function translator()
	{
		return $this->app['translator'];
	}

	public function log()
	{
		return $this->app['monolog'];
	}

	public function isDebug()
	{
		return $this->app['debug'];
	}

	public function json($data = array(), $status = 200, array $headers = array())
	{
		return $this->app->json($data, $status, $headers);
	}

	public function redirect($url, $status = 302)
	{
		return $this->app->redirect($url, $status);
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
