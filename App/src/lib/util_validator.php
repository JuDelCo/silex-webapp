<?php

use Silex\Application;

class ValidatorHelperUtil
{
	protected $app;

	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	// Comprueba una expresión regular
	public function regEx($value, $regEx)
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

	public function isInteger($value)
	{
		if(is_int($value))
		{
			return true;
		}

		return false;
	}

	public function isDecimal($value)
	{
		if(is_float($value))
		{
			return true;
		}

		return false;
	}

	public function isNumeric($value)
	{
		if($this->isInteger($value) || $this->isDecimal($value))
		{
			return true;
		}

		return false;
	}

	public function isString($value)
	{
		if(is_string($value))
		{
			return true;
		}

		return false;
	}

	public function isArray($value)
	{
		if(is_array($value))
		{
			return true;
		}

		return false;
	}

	public function isBool($value)
	{
		if($value === true || $value === false)
		{
			return true;
		}

		return false;
	}

	public function isEmpty($value, $trim = false)
	{
		if($trim && $this->isString($value))
		{
			$value = trim($value);
		}

		return empty($value);
	}

	public function isNotEmpty($value, $trim = false)
	{
		return (!$this->isEmpty($value, $trim));
	}

	public function isNull($value)
	{
		return is_null($value);
	}

	public function isNotNull($value)
	{
		return (!$this->isNull($value));
	}

	public function isEmail($value)
	{
		if($this->isString($value) && $this->isNotEmpty($value))
		{
			$email_regex  = '/^([0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9})$/';

			return $this->regEx($value, $email_regex);
		}
	
		return false;
	}

	public function isDate($date, $format = 'd/m/Y')
	{
		if($this->isString($date))
		{
			$dateTime = \DateTime::createFromFormat($format, $date);
			
			return ($dateTime && ($dateTime->format($format) == $date));
		}

		return false;
	}

	public function isDateTime($date, $format = 'd/m/Y H:i:s')
	{
		return $this->isDate($date, $format);
	}

	public function isAlphaNumeric($value, $allowSpaces = true, $allowUnderscores = true)
	{
		$regex = '/^[a-zA-Z0-9' . ($allowSpaces ? ' ' : '') . ($allowUnderscores ? '_' : '') . ']*$/';
		
		return $this->regEx($value, $regex);
	}

	public function isJson($value)
	{
		json_decode($value);

		return (json_last_error() == JSON_ERROR_NONE);
	}
}
