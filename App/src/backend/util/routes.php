<?php

require_once __DIR__.'/util.php';

$util = $app['controllers_factory'];

$util->post('/ajax/', 'ApiUtil::ajax_data')
->bind('rta_util_ajax');

$util->before(function () use ($app)
{
	$role_controlled_routes = array(
		array(
			'routes' => array('rta_util_ajax')
		)
	);

	return $app['auth']->firewall($role_controlled_routes);
});

return $util;
