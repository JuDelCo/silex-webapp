<?php

namespace Src\Backend\Util;

use Silex\Application;

class UtilAjax
{
	static public function get_data(Application $app, $request_info)
	{
		$data = array('data' => array(), 'error' => '');

		$request_id = $request_info['request_id'];
		$request_filters = $request_info['request_filters'];

		if(empty($request_id))
		{
			$data['error'] = 'No se ha especificado ningun request_id';

			return $data;
		}

		switch ($request_id)
		{
			// ----------------------------------
			case 'EXAMPLE_REQUEST_ID':
				if($app['validator']->isEmpty($request_info['request_filters']['EXAMPLE_ID'], true))
				{
					$data['error'] = 'EXAMPLE_ID no especificado';
					break;
				}

				// 	$sql = "id, value";
				// 	$data['data'] = $app['sql']->getAll($sql);

				break;
			// ----------------------------------
			default:
				$error_msg = 'Peticion AJAX con request_id invalido ("' . $request_id . '")';
				$data['error'] = $error_msg;

				break;
		}

		return $data;
	}
}
