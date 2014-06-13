<?php

namespace Src\Lib;

use Src\Lib\Application;

class ValidatorHelper extends Application
{
	// Comprueba una expresión regular
	function regEx($value, $regEx)
	{
		if($this->isString($value))
		{
			$result = preg_match($regEx, $value);

			if($result)
			{
				return true;
			}
		}

		return false;
	}

	function isInteger($value)
	{
		if(is_int($value))
		{
			return true;
		}

		return false;
	}

	function isDecimal($value)
	{
		if(is_float($value))
		{
			return true;
		}

		return false;
	}

	function isNumeric($value)
	{
		if($this->isInteger($value) || $this->isDecimal($value))
		{
			return true;
		}

		return false;
	}

	function isString($value)
	{
		if(is_string($value))
		{
			return true;
		}

		return false;
	}

	function isArray($value)
	{
		if(is_array($value))
		{
			return true;
		}

		return false;
	}

	function isBool($value)
	{
		if($value === true || $value === false)
		{
			return true;
		}

		return false;
	}

	function isScalar($value)
	{
		return is_scalar($value);
	}

	function isEmpty($value, $trim = false)
	{
		if($trim && $this->isString($value))
		{
			$value = trim($value);
		}

		return empty($value);
	}

	function isNotEmpty($value, $trim = false)
	{
		return (!$this->isEmpty($value, $trim));
	}

	function isNull($value)
	{
		return is_null($value);
	}

	function isNotNull($value)
	{
		return (!$this->isNull($value));
	}

	function isEmail($value)
	{
		if($this->isString($value) && $this->isNotEmpty($value))
		{
			$email_regex = '/^([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9})$/';

			return $this->regEx($value, $email_regex);
		}

		return false;
	}

	function isUrl($value, $protocols = 'http|https')
	{
		if($this->isString($value) && $this->isNotEmpty($value))
		{
			$url_regex = '~^
					(%s)://                                 # protocol
					(
						([\pL\pN\pS-]+\.)+[\pL]+            # a domain name
							|                               #  or
						\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}  # a IP address
							|                               #  or
						\[
							(?:(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){6})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:::(?:(?:(?:[0-9a-f]{1,4})):){5})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){4})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,1}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){3})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,2}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){2})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,3}(?:(?:[0-9a-f]{1,4})))?::(?:(?:[0-9a-f]{1,4})):)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,4}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,5}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,6}(?:(?:[0-9a-f]{1,4})))?::))))
						\]                                  # a IPv6 address
					)
					(:[0-9]+)?                              # a port (optional)
					(/?|/\S+)                               # a /, nothing or a / with something
				$~ixu';

			return $this->regEx($value, sprintf($url_regex, $protocols));
		}

		return false;
	}

	function isDate($date, $format = 'd/m/Y')
	{
		if($this->isString($date))
		{
			$dateTime = \DateTime::createFromFormat($format, $date);

			return ($dateTime && ($dateTime->format($format) == $date));
		}

		return false;
	}

	function isDateTime($date, $format = 'd/m/Y H:i:s')
	{
		return $this->isDate((string)$date, $format);
	}

	function isAlphaNumeric($value, $allowSpaces = true, $allowUnderscores = true)
	{
		$regex = '/^[a-zA-Z0-9' . ($allowSpaces ? ' ' : '') . ($allowUnderscores ? '_' : '') . ']*$/';

		return $this->regEx((string)$value, $regex);
	}

	function isJson($value)
	{
		json_decode($value);

		return (json_last_error() == JSON_ERROR_NONE);
	}
}
