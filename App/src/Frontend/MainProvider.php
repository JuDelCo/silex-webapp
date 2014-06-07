<?php

namespace Src\Frontend;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Src\Frontend\Main;

class MainProvider implements ControllerProviderInterface
{
	public function connect(Application $app)
	{
		$app['controller.main'] = $app->share(function() use ($app)
		{
			return new Main($app);
		});

		$main = $app['controllers_factory'];

		$main->get('/', 'controller.main:root')
		->bind('rt_root');
		$main->get('/index.{extension}', 'controller.main:index')
		->bind('rt_index')->assert('extension', '^(php)|(html)$');
		$main->get('/inicio/', 'controller.main:main')
		->bind('rt_main');

		$main->before(function () use ($app)
		{
			$anonymous_routes = array('rt_root','rt_index');
			$role_controlled_routes = array(
				array(
					'routes' => array('rt_main')
				)
			);

			return $app['auth']->firewall($role_controlled_routes, $anonymous_routes);
		});

		return $main;
	}
}
