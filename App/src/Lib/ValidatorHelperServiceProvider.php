<?php

namespace Src\Lib;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Src\Lib\ValidatorHelper;

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
