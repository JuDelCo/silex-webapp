<?php

use Silex\Application;
use Silex\ServiceProviderInterface;

require_once __DIR__.'/validator.php';

class ValidatorHelperServiceProvider implements ServiceProviderInterface
{
	public function register(Application $app)
	{
		$app['validator'] = $app->share(function ($app)
		{
			$ValidatorHelper = new ValidatorHelper($app);

			return $ValidatorHelper;
		});
	}

	public function boot(Application $app) {}
}
