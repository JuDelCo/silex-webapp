<?php

use Silex\Application;

require_once __DIR__.'/util_ajax.php';

class ApiUtilController
{
	public function ajax_data(Application $app)
	{
		$request_info = array(
			'request_id' =>      $app['request']->request->get('request_id', NULL),
			'request_type' =>    $app['request']->request->get('request_type', NULL),
			'request_filters' => $app['request']->request->get('request_filters', NULL),
			'request_options' => $app['request']->request->get('request_options', NULL)
		);

		$data = ApiUtilAjaxQuery::get_data($app, $request_info);

		if($request_info['request_type'] == 'options' && empty($data['error']))
		{
			$data['html_options'] = $app['twig']->render('util/html_options.twig', 
				array('data' => $data['data'], 'options' => $request_info['request_options']));

			unset($data['data']);
		}

		return $app->json($data, (empty($data['error']) ? 200 : 400));
	}
}
