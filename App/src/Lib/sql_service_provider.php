<?php

use Silex\Application;
use Silex\ServiceProviderInterface;

require_once __DIR__.'/sql.php';

class SqlHelperServiceProvider implements ServiceProviderInterface
{
	public function register(Application $app)
	{
		$app['sql'] = $app->share(function ($app)
		{
			$SqlHelper = new SqlHelper($app);

			return $SqlHelper;
		});
	}

	public function boot(Application $app) {}
}
