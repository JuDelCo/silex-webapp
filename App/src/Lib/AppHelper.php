<?php

namespace Src\Lib;

use Src\Lib\Application;

class AppHelper extends Application
{
	function getClientIp()
	{
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && eregi("^[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}$",$_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else
		{
			$ip = getenv("REMOTE_ADDR");
		}

		return $ip;
	}

	function getHostname($ip)
	{
		return gethostbyaddr($ip);
	}

	function getClientHostname()
	{
		return $this->getHostname($this->getClientIp());
	}

	// Envuelve la variable proporcionada entre 2 tags HTML (útil para debugguear)
	function wrap($value, $wrap_start = '<pre>', $wrap_end = '</pre>')
	{
		if($this->validator()->isArray($value))
		{
			return $wrap_start . print_r($value, true) . $wrap_end;
		}
		else if($this->validator()->isString($value))
		{
			return $wrap_start . $value . $wrap_end;
		}
		else
		{
			return $wrap_start . $wrap_end;
		}
	}
}
