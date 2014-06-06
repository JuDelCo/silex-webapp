<?php

use Silex\Application;
use Silex\ServiceProviderInterface;

require_once __DIR__.'/app.php';

class AppHelperServiceProvider implements ServiceProviderInterface
{
	public function register(Application $app)
	{
		$app['util'] = $app->share(function ($app)
		{
			$AppHelper = new AppHelper($app);

			return $AppHelper;
		});
	}

	public function boot(Application $app) {}
}
