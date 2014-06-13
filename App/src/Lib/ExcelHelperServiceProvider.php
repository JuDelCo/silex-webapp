<?php

namespace Src\Lib;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Src\Lib\ExcelHelper;

class ExcelHelperServiceProvider implements ServiceProviderInterface
{
	function register(Application $app)
	{
		$app['excel'] = $app->share(function ($app)
		{
			$ExcelHelper = new ExcelHelper($app);

			return $ExcelHelper;
		});
	}

	function boot(Application $app) {}
}
