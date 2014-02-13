<?php

use Silex\Application;

class AppHelperUtil
{
	protected $app;

	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	// Envuelve la variable proporcionada entre 2 tags HTML (útil para debugguear)
	public function wrap($value, $wrap_start = '<pre>', $wrap_end = '</pre>')
	{
		if($this->app['validator']->isArray($value))
		{
			return $wrap_start . print_r($value, true) . $wrap_end;
		}
		else if($this->app['validator']->isString($value))
		{
			return $wrap_start . $value . $wrap_end;
		}
		else
		{
			return $wrap_start . $wrap_end;
		}
	}
}
