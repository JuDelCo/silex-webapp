<?php

namespace Src\Lib;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Src\Lib\AuthHelper;

class AuthHelperServiceProvider implements ServiceProviderInterface
{
	public function register(Application $app)
	{
		$app['auth'] = $app->share(function ($app)
		{
			$AuthHelper = new AuthHelper($app);

			return $AuthHelper;
		});
	}

	public function boot(Application $app) {}
}
