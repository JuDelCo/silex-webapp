<?php

namespace Src\Lib;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Src\Lib\SqlHelper;

class SqlHelperServiceProvider implements ServiceProviderInterface
{
	function register(Application $app)
	{
		$app['sql'] = $app->share(function ($app)
		{
			$SqlHelper = new SqlHelper($app);

			return $SqlHelper;
		});
	}

	function boot(Application $app) {}
}
