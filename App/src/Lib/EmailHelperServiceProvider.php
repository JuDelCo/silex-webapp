<?php

namespace Src\Lib;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Src\Lib\EmailHelper;

class EmailHelperServiceProvider implements ServiceProviderInterface
{
	public function register(Application $app)
	{
		$app['email'] = $app->share(function ($app)
		{
			$EmailHelper = new EmailHelper($app);

			return $EmailHelper;
		});
	}

	public function boot(Application $app) {}
}
