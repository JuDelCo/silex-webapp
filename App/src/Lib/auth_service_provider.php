<?php

use Silex\Application;
use Silex\ServiceProviderInterface;

require_once __DIR__.'/auth.php';

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
