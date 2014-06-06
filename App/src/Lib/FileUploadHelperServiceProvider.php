<?php

namespace Src\Lib;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Src\Lib\FileUploadHelper;

class FileUploadHelperServiceProvider implements ServiceProviderInterface
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
