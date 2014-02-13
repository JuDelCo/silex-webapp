<?php

use Silex\Application;
use Silex\ServiceProviderInterface;

require_once __DIR__.'/util_sql.php';
require_once __DIR__.'/util_validator.php';
require_once __DIR__.'/util_app.php';
require_once __DIR__.'/util_auth.php';
require_once __DIR__.'/util_email.php';
require_once __DIR__.'/util_twig.php';

class SqlHelperUtilServiceProvider implements ServiceProviderInterface
{
	public function register(Application $app)
	{
		$app['sql'] = $app->share(function ($app)
		{
			$SqlHelperUtilClass = new SqlHelperUtil($app);

			return $SqlHelperUtilClass;
		});
	}

	public function boot(Application $app) {}
}

class ValidatorHelperUtilServiceProvider implements ServiceProviderInterface
{
	public function register(Application $app)
	{
		$app['validator'] = $app->share(function ($app)
		{
			$ValidatorHelperUtilClass = new ValidatorHelperUtil($app);

			return $ValidatorHelperUtilClass;
		});
	}

	public function boot(Application $app) {}
}

class AppHelperUtilServiceProvider implements ServiceProviderInterface
{
	public function register(Application $app)
	{
		$app['util'] = $app->share(function ($app)
		{
			$AppHelperUtilClass = new AppHelperUtil($app);

			return $AppHelperUtilClass;
		});
	}

	public function boot(Application $app) {}
}

class AuthHelperUtilServiceProvider implements ServiceProviderInterface
{
	public function register(Application $app)
	{
		$app['auth'] = $app->share(function ($app)
		{
			$AuthHelperUtilClass = new AuthHelperUtil($app);

			return $AuthHelperUtilClass;
		});
	}

	public function boot(Application $app) {}
}

class EmailHelperUtilServiceProvider implements ServiceProviderInterface
{
	public function register(Application $app)
	{
		$app['email'] = $app->share(function ($app)
		{
			$EmailHelperUtilClass = new EmailHelperUtil($app);

			return $EmailHelperUtilClass;
		});
	}

	public function boot(Application $app) {}
}
