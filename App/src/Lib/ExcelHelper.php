<?php

namespace Src\Lib;

use Src\Lib\Application;
use Symfony\Component\Filesystem\Filesystem;

class ExcelHelper extends Application
{
	protected $objPHPExcel;

	function __construct($app)
	{
		parent::__construct($app);

		// Caché habilitada por defecto !
		if(! $this->app('excel.disable_cache'))
		{
			// Opcional: Sin caché es 2 veces más rapido, pero consume 3 veces más RAM !
			$cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_to_sqlite3;
			\PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
		}

		$this->objPHPExcel = new \PHPExcel();
		$this->reset(true);
	}

	// General ----------------------------------

	function getExcelObj()
	{
		return $this->objPHPExcel;
	}

	function getExcelWriter()
	{
		$objWriter = \PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
		$objWriter->setPreCalculateFormulas(false);

		return $objWriter;
	}

	function reset($reset_metadata = false)
	{
		if($reset_metadata)
		{
			$this->setCreator("");
			$this->setTitle("Sin Título");
			$this->setSubject("Sin Asunto");
			$this->setDescription("Excel automática");
			$this->setKeywords("excel exportacion informe documento office");
			$this->setCategory("Excel Office 2007");
		}

		for ($i = $this->objPHPExcel->getSheetCount(); $i > 0; $i--)
		{
			$this->objPHPExcel->removeSheetByIndex($i - 1);
		}

		$this->objPHPExcel->createSheet();

		$this->setDefaultHeight(15);
	}

	function intToCellColumn($number)
	{
		for($column = ""; $number >= 0; $number = intval($number / 26) - 1)
		{
			$column = chr(($number % 26) + 0x41) . $column;
		}

		return $column;
	}

	function setCellValue($cell, $value, $sheetIndex = 0)
	{
		$this->objPHPExcel->setActiveSheetIndex($sheetIndex)->setCellValue($cell, $value);
	}

	function writeData($data, $sheetIndex = 0, $position = 'A1')
	{
		if(! $this->validator()->isArray($data) || empty($data))
		{
			$this->setCellValue($position, '', $sheetIndex);

			return false;
		}

		$this->objPHPExcel->setActiveSheetIndex($sheetIndex)->fromArray($data, NULL, $position);

		return true;
	}

	function writeSqlData($data, $sheetIndex = 0, $position = 'A1')
	{
		if(! $this->validator()->isArray($data) || empty($data))
		{
			$this->setCellValue($position, '', $sheetIndex);

			return false;
		}

		$title_array = array();

		foreach ($data[0] as $key => $value)
		{
			$title_array[] = $key;
		}

		array_unshift($data, $title_array);

		return $this->writeData($data, $sheetIndex, $position);
	}

	function setCursorPosition($cell = 'A1', $sheetIndex = 0)
	{
		$this->objPHPExcel->setActiveSheetIndex($sheetIndex)->getStyle($cell);
	}

	function saveToFile($name, $tipo = 'general', $root_path = null)
	{
		$sql = "SELECT top 1 tipo_excel_id from excel_tipo where nombre = '$tipo'";
		$tipo_excel_id = $this->db()->getValue($sql);
		$excel_path = '';

		if(! $root_path)
		{
			$root_path = $this->app('path.info')['root'] . $this->app('path.info')['excel'];
		}

		$fs = new Filesystem();

		do
		{
			$excel_path = $root_path . $this->auth()->generateToken() . '.xlsx';
		}
		while($fs->exists($excel_path));

		$this->setCursorPosition('A1');

		$objWriter = \PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
		$objWriter->setPreCalculateFormulas(false);
		$objWriter->save($excel_path);

		$sql = "INSERT into excel (usuario_id, tipo_excel_id, nombre, ruta, fecha, existe_sn)
			values
			(
				" . $this->getSessionData('user_real.id') . ",
				" . ($tipo_excel_id ? $tipo_excel_id : 'null') . ",
				'$name',
				'$excel_path',
				'" . date('d/m/Y H:i:s') . "',
				1
			)";

		return $this->db()->runInsert($sql);
	}

	function download($excel_id)
	{
		$excel_id = intval($excel_id);

		$sql = "SELECT tipo_excel_id, nombre, ruta, fecha, existe_sn
			from excel
			where excel_id = $excel_id
				and existe_sn = 1
				and usuario_id = " . $this->getSessionData('user.id');
		$excel_data = $this->db()->getFirstRow($sql);

		if(! $excel_data)
		{
			return $this->jsonResponse(array('error' => 'No se ha encontrado la excel'), 400);
		}

		$fs = new Filesystem();

		if (! $fs->exists($excel_data['ruta']))
		{
			return $this->jsonResponse(array('error' => 'No existe el archivo'), 400);
		}

		$size = filesize($excel_data['ruta']);
		$excel_string = file_get_contents($excel_data['ruta']);

		$excel_filename = stripslashes($excel_data['nombre']);

		$user_agent = $this->request()->server->get('HTTP_USER_AGENT');

		// Arreglamos el nombre del archivo si es Internet Explorer...
		if (isset($user_agent) && (strpos($user_agent, 'MSIE') !== false))
		{
			$excel_filename = rawurlencode($excel_filename);
		}

		$response = $this->response($excel_string);

		$response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8');
		$response->headers->set('Content-Disposition', 'attachment; filename="' . $excel_filename . '.xlsx"');
		$response->headers->set('Content-Transfer-Encoding', 'binary');
		$response->headers->set('Content-Length', $size);

		return $response;
	}

	function getResponse($excel_filename)
	{
		$this->setCursorPosition('A1');

		$response = $this->response();
		$response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8');
		$response->headers->set('Content-Disposition', "attachment;filename=$excel_filename.xlsx");
		$response->headers->set('Cache-Control', 'max-age=0');

		$objWriter = \PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
		$objWriter->setPreCalculateFormulas(false);

		ob_start();
		$objWriter->save('php://output');
		$response->setContent(ob_get_clean());

		return $response;
	}

	// Metadata ---------------------------------

	function setCreator($username)
	{
		$this->objPHPExcel->getProperties()->setCreator($username)->setLastModifiedBy($username);
	}

	function setTitle($title)
	{
		$this->objPHPExcel->getProperties()->setTitle($title);
	}

	function setSubject($subject)
	{
		$this->objPHPExcel->getProperties()->setSubject($subject);
	}

	function setDescription($description)
	{
		$this->objPHPExcel->getProperties()->setDescription($description);
	}

	function setKeywords($keywords)
	{
		$this->objPHPExcel->getProperties()->setKeywords($keywords);
	}

	function setCategory($category)
	{
		$this->objPHPExcel->getProperties()->setCategory($category);
	}

	function setSheetTitle($title, $sheetIndex = 0)
	{
		$this->objPHPExcel->setActiveSheetIndex($sheetIndex)->setTitle($title);
	}

	function setZoom($zoom, $sheetIndex = 0)
	{
		$this->objPHPExcel->setActiveSheetIndex($sheetIndex)->getSheetView()->setZoomScale($zoom);
	}

	// Estilos ----------------------------------

	function setDefaultHeight($height = 15, $sheetIndex = 0)
	{
		$this->objPHPExcel->setActiveSheetIndex($sheetIndex)->getDefaultRowDimension()->setRowHeight($height);
	}

	function setWidth($column, $width = 12, $sheetIndex = 0)
	{
		$this->objPHPExcel->setActiveSheetIndex($sheetIndex)->getColumnDimension($column)->setWidth($width);
	}

	function setAutoSize($column, $autosize = false, $sheetIndex = 0)
	{
		$this->objPHPExcel->setActiveSheetIndex($sheetIndex)->getColumnDimension($column)->setAutoSize($autosize);
	}

	function setVisible($column, $visible = true, $sheetIndex = 0)
	{
		$this->objPHPExcel->setActiveSheetIndex($sheetIndex)->getColumnDimension($column)->setVisible($visible);
	}

	function setCellUrl($cell, $url, $sheetIndex = 0)
	{
		$this->objPHPExcel->setActiveSheetIndex($sheetIndex)->getCell($cell)->getHyperlink()->setUrl($url);
	}

	function setFont($fontName, $cellRange, $sheetIndex = 0)
	{
		$this->objPHPExcel->setActiveSheetIndex($sheetIndex)->getStyle($cellRange)->getFont()->setName($fontName);
	}

	function setBold($bold, $cellRange, $sheetIndex = 0)
	{
		$this->objPHPExcel->setActiveSheetIndex($sheetIndex)->getStyle($cellRange)->getFont()->setBold($bold);
	}

	function setColor($color, $cellRange, $sheetIndex = 0)
	{
		$this->objPHPExcel->setActiveSheetIndex($sheetIndex)->getStyle($cellRange)->getFont()->getColor()->setRGB($color);
	}

	function setBorder($side, $type, $cellRange, $sheetIndex = 0)
	{
		$borderObj;

		switch ($side)
		{
			case 'top':
				$borderObj = $this->objPHPExcel->setActiveSheetIndex($sheetIndex)->getStyle($cellRange)->getBorders()->getTop();
				break;
			case 'left':
				$borderObj = $this->objPHPExcel->setActiveSheetIndex($sheetIndex)->getStyle($cellRange)->getBorders()->getLeft();
				break;
			case 'right':
				$borderObj = $this->objPHPExcel->setActiveSheetIndex($sheetIndex)->getStyle($cellRange)->getBorders()->getRight();
				break;
			case 'bottom':
				$borderObj = $this->objPHPExcel->setActiveSheetIndex($sheetIndex)->getStyle($cellRange)->getBorders()->getBottom();
				break;
			case 'all':
				$borderObj = $this->objPHPExcel->setActiveSheetIndex($sheetIndex)->getStyle($cellRange)->getBorders()->getAllBorders();
			default:
				return;
				break;
		}

		switch ($type)
		{
			case 'thin':
				$borderObj->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
				break;
			case 'thick':
				$borderObj->setBorderStyle(\PHPExcel_Style_Border::BORDER_THICK);
				break;
			case 'medium':
				$borderObj->setBorderStyle(\PHPExcel_Style_Border::BORDER_MEDIUM);
				break;
			case 'double':
				$borderObj->setBorderStyle(\PHPExcel_Style_Border::BORDER_DOUBLE);
				break;
			case 'none':
			default:
				$borderObj->setBorderStyle(\PHPExcel_Style_Border::BORDER_NONE);
				break;
		}
	}

	function setHorizontalAlign($type, $cellRange, $sheetIndex = 0)
	{
		$alignment = $this->objPHPExcel->setActiveSheetIndex($sheetIndex)->getStyle($cellRange)->getAlignment();

		switch ($type)
		{
			case 'left':
				$alignment->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				break;
			case 'center':
				$alignment->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				break;
			case 'right':
				$alignment->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				break;
			case 'justify':
				$alignment->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY);
				break;
			default:
				$alignment->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_GENERAL);
				break;
		}
	}
}
