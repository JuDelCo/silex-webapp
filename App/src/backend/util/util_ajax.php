<?php

use Silex\Application;

class ApiUtilAjaxQuery
{
	static public function get_data(Application $app, $request_info)
	{
		$data = array('data' => array(), 'error' => '');

		if(empty($request_info['request_id']))
		{
			$data['error'] = 'No se ha especificado ningun request_id';

			return $data;
		}

		switch ($request_info['request_id'])
		{
			// ----------------------------------
			case 'EXAMPLE_REQUEST_ID':
				if($app['validator']->isEmpty($request_info['request_filters']['EXAMPLE_ID'], true))
				{
					$data['error'] = 'EXAMPLE_ID no especificado';
					break;
				}

				// TODO
				// 		$sql = "id, value";
				// 		$data['data'] = $app['sql']->getAll($sql);

				break;
			// ----------------------------------
			default:
				$data['error'] = 'Peticion AJAX con request_id invalido ("' . $request_info['request_id'] . '")';

				break;
		}

		return $data;
	}
}
