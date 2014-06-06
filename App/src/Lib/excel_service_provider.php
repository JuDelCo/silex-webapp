<?php

use Silex\Application;
use Silex\ServiceProviderInterface;

require_once __DIR__.'/excel.php';

class ExcelHelperServiceProvider implements ServiceProviderInterface
{
	public function register(Application $app)
	{
		$app['excel'] = $app->share(function ($app)
		{
			$ExcelHelper = new ExcelHelper($app);

			return $ExcelHelper;
		});
	}

	public function boot(Application $app) {}
}
