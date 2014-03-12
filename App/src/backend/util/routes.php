<?php

require_once __DIR__.'/util.php';

$util_controller = $app['controllers_factory'];

$util_controller->post('/ajax/', 'ApiUtilController::ajax_data')
->bind('rta_util_ajax');

$util_controller->before(function () use ($app)
{
	$role_controlled_routes = array(
		array(
			'routes' => array('rta_util_ajax')
		)
	);

	return $app['auth']->firewall($role_controlled_routes);
});

return $util_controller;
