<?php

namespace Src\Backend\Util;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Src\Backend\Util\ApiUtil;

class ApiUtilProvider implements ControllerProviderInterface
{
	public function connect(Application $app)
	{
		$app['controller.api.util'] = $app->share(function() use ($app)
		{
			return new ApiUtil($app);
		});

		$util = $app['controllers_factory'];

		$util->post('/ajax_datos/', 'controller.api.util:ajax_datos')
		->bind('rta_util_ajax_datos');
		$util->match('/excel/json/', 'controller.api.util:excel_json')
		->bind('rta_util_excel_json')->method('GET|POST');

		$util->before(function () use ($app)
		{
			$anonymous_routes = array('rta_util_excel_json');

			$role_controlled_routes = array(
				array(
					'routes' => array('rta_util_ajax_datos')
				)
			);

			return $app['auth']->firewall($role_controlled_routes, $anonymous_routes);
		});

		return $util;
	}
}
