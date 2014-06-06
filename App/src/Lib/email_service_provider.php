<?php

use Silex\Application;
use Silex\ServiceProviderInterface;

require_once __DIR__.'/email.php';

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
