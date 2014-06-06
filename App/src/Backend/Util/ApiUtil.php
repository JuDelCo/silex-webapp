<?php

namespace Src\Backend\Util;

use Silex\Application;
use Src\Backend\Util\UtilAjax;

class ApiUtil
{
	public function ajax_datos(Application $app)
	{
		$request_info = array(
			'request_id' => $app['request']->request->get('request_id', NULL),
			'request_type' => $app['request']->request->get('request_type', NULL),
			'request_filters' => $app['request']->request->get('request_filters', NULL),
			'request_options' => $app['request']->request->get('request_options', NULL)
		);

		// Desbloqueamos la sesión (para que se puedan realizar otras peticiones al servidor)
		$app['session']->save(); // session_write_close();

		try
		{
			$data = UtilAjax::get_data($app, $request_info);
		}
		catch (\Exception $e)
		{
			$data['error'] = $e->getMessage();
		}

		if($request_info['request_type'] == 'options' && empty($data['error']))
		{
			$data['html_options'] = $app['twig']->render('util/html_options.twig',
				array('data' => $data['data'], 'options' => $request_info['request_options']));

			unset($data['data']);
		}

		return $app->json($data, (empty($data['error']) ? 200 : 400));
	}

	public function excel_json(Application $app)
	{
		// Ejemplo:
		// 		/api/util/excel/json/?data=[{%22campo1%22:%20%22valor1%22,%22campo2%22:%20%22valor2%22,%22campo3%22:%20%22valor3%22}]

		$data = array();

		if($app['request']->getMethod() == "GET")
		{
			$data = $app['request']->query->get('data', array());
		}
		else
		{
			$data = $app['request']->request->get('data', array());
		}

		if(! $app['validator']->isArray($data))
		{
			try
			{
				$data = json_decode($data, true);
			}
			catch (\Exception $e)
			{
				return $app->json(array('error' => 'Error al parsear el JSON'), 400);
			}
		}

		if(empty($data))
		{
			return $app->json(array('error' => 'Debes especificar los datos a convertir'), 400);
		}

		$app['excel']->writeData($data);

		return $app['excel']->getResponse(date('Y-m-d_H-i-s') . '_excel_json');
	}
}
