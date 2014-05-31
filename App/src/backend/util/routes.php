<?php

require_once __DIR__.'/util.php';

$util = $app['controllers_factory'];

$util->post('/ajax_datos/', 'ApiUtil::ajax_datos')
->bind('rta_util_ajax_datos');
$util->match('/excel/json/', 'ApiUtil::excel_json')
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
