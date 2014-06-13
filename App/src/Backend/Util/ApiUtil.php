<?php

namespace Src\Backend\Util;

use Src\Lib\Application;
use Src\Backend\Util\UtilAjax;

class ApiUtil extends Application
{
	function ajax_datos()
	{
		$request_info = array(
			'request_id' => $this->requestData('POST', 'request_id', NULL),
			'request_type' => $this->requestData('POST', 'request_type', NULL),
			'request_filters' => $this->requestData('POST', 'request_filters', NULL),
			'request_options' => $this->requestData('POST', 'request_options', NULL)
		);

		// Desbloqueamos la sesión (para que se puedan realizar otras peticiones al servidor)
		$this->session()->save(); // session_write_close();

		try
		{
			$data = UtilAjax::get_data($this->app(), $request_info);
		}
		catch (\Exception $e)
		{
			$data['error'] = $e->getMessage();
		}

		if($request_info['request_type'] == 'options' && empty($data['error']))
		{
			$data['html_options'] = $this->render('util/html_options.twig',
				array('data' => $data['data'], 'options' => $request_info['request_options']));

			unset($data['data']);
		}

		return $this->jsonResponse($data, (empty($data['error']) ? 200 : 400));
	}

	function excel_json()
	{
		// Ejemplo:
		// 		/api/util/excel/json/?data=[{%22campo1%22:%20%22valor1%22,%22campo2%22:%20%22valor2%22,%22campo3%22:%20%22valor3%22}]

		$data = array();

		$data = $this->requestData($this->request()->getMethod(), 'data', array());

		if(! $this->validator()->isArray($data))
		{
			try
			{
				$data = json_decode($data, true);
			}
			catch (\Exception $e)
			{
				return $this->jsonResponse(array('error' => 'Error al parsear el JSON'), 400);
			}
		}

		if(empty($data))
		{
			return $this->jsonResponse(array('error' => 'Debes especificar los datos a convertir'), 400);
		}

		$this->excel()->writeData($data);

		return $this->excel()->getResponse(date('Y-m-d_H-i-s') . '_excel_json');
	}
}
