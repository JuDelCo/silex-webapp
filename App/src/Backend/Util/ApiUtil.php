<?php

namespace Src\Backend\Util;

use Src\Lib\ControllerBase;
use Silex\Application;
use Src\Backend\Util\UtilAjax;

class ApiUtil extends ControllerBase
{
	public function ajax_datos()
	{
		$request_info = array(
			'request_id' => $this->request()->request->get('request_id', NULL),
			'request_type' => $this->request()->request->get('request_type', NULL),
			'request_filters' => $this->request()->request->get('request_filters', NULL),
			'request_options' => $this->request()->request->get('request_options', NULL)
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
			$data['html_options'] = $this->twig()->render('util/html_options.twig',
				array('data' => $data['data'], 'options' => $request_info['request_options']));

			unset($data['data']);
		}

		return $this->app()->json($data, (empty($data['error']) ? 200 : 400));
	}

	public function excel_json()
	{
		// Ejemplo:
		// 		/api/util/excel/json/?data=[{%22campo1%22:%20%22valor1%22,%22campo2%22:%20%22valor2%22,%22campo3%22:%20%22valor3%22}]

		$data = array();

		if($this->request()->getMethod() == "GET")
		{
			$data = $this->request()->query->get('data', array());
		}
		else
		{
			$data = $this->request()->request->get('data', array());
		}

		if(! $this->validator()->isArray($data))
		{
			try
			{
				$data = json_decode($data, true);
			}
			catch (\Exception $e)
			{
				return $this->app()->json(array('error' => 'Error al parsear el JSON'), 400);
			}
		}

		if(empty($data))
		{
			return $this->app()->json(array('error' => 'Debes especificar los datos a convertir'), 400);
		}

		$this->excel()->writeData($data);

		return $this->excel()->getResponse(date('Y-m-d_H-i-s') . '_excel_json');
	}
}
