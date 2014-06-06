<?php

use Silex\Application;
use Silex\ServiceProviderInterface;

require_once __DIR__.'/fileupload.php';

class UploadHelperServiceProvider implements ServiceProviderInterface
{
	public function register(Application $app)
	{
		$app['upload'] = $app->share(function ($app)
		{
			$FileUploadHelper = new FileUploadHelper($app);

			return $FileUploadHelper;
		});
	}

	public function boot(Application $app) {}
}
