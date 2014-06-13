<?php

namespace Src\Lib;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Src\Lib\FileUploadHelper;

class FileUploadHelperServiceProvider implements ServiceProviderInterface
{
	function register(Application $app)
	{
		$app['upload'] = $app->share(function ($app)
		{
			$FileUploadHelper = new FileUploadHelper($app);

			return $FileUploadHelper;
		});
	}

	function boot(Application $app) {}
}
