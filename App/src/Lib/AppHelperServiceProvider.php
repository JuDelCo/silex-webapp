<?php

namespace Src\Lib;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Src\Lib\AppHelper;

class AppHelperServiceProvider implements ServiceProviderInterface
{
	function register(Application $app)
	{
		$app['util'] = $app->share(function ($app)
		{
			$AppHelper = new AppHelper($app);

			return $AppHelper;
		});
	}

	function boot(Application $app) {}
}
