<?php

use Silex\Application;

class SqlHelperUtil
{
	protected $app;

	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	// Conecta a la base de datos
	public function connect()
	{
		return $this->app['db']->connect();
	}

	// Comprueba si está conectado a la base de datos
	public function isConnected()
	{
		return $this->app['db']->isConnected();
	}

	// Ejecuta la sentencia SQL y devuelve el numero total de filas afectadas (0 si no afectó a ninguna)
	public function run($sql)
	{
		return $this->app['db']->exec($sql);
	}

	// Array de resultados (Array vacío si no devuelve ninguno)
	public function getAll($sql)
	{
		return $this->app['db']->fetchAll($sql);
	}

	// Array con la primera fila de los resultados (Array vacío si no devuelve ninguno)
	public function getFirstRow($sql)
	{
		$rows = $this->getAll($sql);

		if($rows)
		{
			return $rows[0];
		}

		return $rows;
	}

	// Devolver valor de la primera columna del primer resultado (false si no devuelve nada)
	public function getValue($sql)
	{
		return $this->app['db']->fetchColumn($sql);
	}

	// Devuelve el ID de la ultima inserción (false si no devuelve nada)
	public function getLastInsertId($tableName = null)
	{
		$lastInsertId = $this->app['db']->lastInsertId($tableName);

		return ($lastInsertId === '' ? false : $lastInsertId);
	}

	// Ejecuta y devuelve el ID de la última fila insertada (false si no insertó nada)
	public function runInsert($sql)
	{
		if ($this->run($sql) > 0)
		{
			return $this->getLastInsertId();
		}

		return false;
	}

	// Devuelve el numero de filas del resultado (0 si no devuelve ninguna)
	public function getRowCount($sql)
	{
		return count($this->getAll($sql));
	}

	// Devuelve true o false dependiendo si la consulta devuelve resultados
	public function hasResults($sql)
	{
		return ($this->getRowCount($sql) ? true : false);
	}

	// Devuelve true o false dependiendo si la consulta no devuelve resultados
	public function hasNotResults($sql)
	{
		return (! $this->hasResults($sql));
	}

	// Empezar una transacción
	public function transBegin()
	{
		return $this->app['db']->beginTransaction();
	}

	// Confirmar una transacción
	public function transCommit()
	{
		return $this->app['db']->commit();
	}

	// Deshacer una transacción
	public function transRollBack()
	{
		return $this->app['db']->rollback();
	}

	// Cambia el modo de devolver los resultados (\PDO::FETCH_ASSOC [Por defecto], \PDO::FETCH_NUM, \PDO::FETCH_BOTH)
	public function setFetchMode($fetchMode)
	{
		$this->app['db']->setFetchMode($fetchMode);
	}

	// Array con información sobre el error
	public function getErrorInfo()
	{
		return $this->app['db']->errorInfo();
	}

	// Transforma el valor en un parámetro válido (y seguro) para concatenar en una SQL
	public function toSQL($value, $appendComma = true)
	{
		if ($value === true)
		{
			$result = 1;
		}
		else if ($value === false)
		{
			$result = 0;
		}
		else if (is_int($value))
		{
			$result = $value;
		}
		else if (is_float($value))
		{
			$result = sprintf('%F', $value);
		}
		else if (is_string($value))
		{
			$result = ("'" . str_replace("'", "''", $value) . "'");

			if($result == "''")
			{
				$result = 'NULL';
			}
		}
		else if (is_numeric($value))
		{
			$result = floatval($value);
		}
		else if (is_array($value))
		{
			$temp = reset($value); // Primer valor del array

			$result = $this->toSQL($temp);
		}
		else
		{
			$result = 'NULL'; // Objetos, Funciones, Nulos, etc...
		}

		return $result . ($appendComma ? ',' : '');
	}

	// Transforma y devuelve un array de valores a parámetros válidos (y seguros) para concatenar en una SQL
	public function toSQLArray($array_values, $appendComma = false)
	{
		$final_array = array();

		if (is_array($array_values))
		{
			foreach ($array_values as $key => $value)
			{
				$final_array[$key] = $this->toSQL($value, $appendComma);
			}
		}

		return $final_array;
	}

	// Genera la cadena SQL necesaria para formatear un campo de fecha (date)
	public function dateFormat($field_name, $alias = null)
	{
		if($alias === null)
		{
			$alias = $field_name;
		}

		if($app['db.options']['driver'] == 'pdo_sqlsrv')
		{
			return "convert(varchar, $field_name, 103) as $alias";
		}
		else // MySQL, others...
		{
			return "date_format($field_name, '%d/%m/%Y') as $alias";
		}
	}

	// Genera la cadena SQL necesaria para formatear un campo de fecha (datetime)
	public function dateTimeFormat($field_name, $alias = null)
	{
		if($alias === null)
		{
			$alias = $field_name;
		}

		if($app['db.options']['driver'] == 'pdo_sqlsrv')
		{
			return "convert(varchar, $field_name, 103) + ' ' + convert(varchar, $field_name, 108) as $alias";
		}
		else // MySQL, others...
		{
			return "date_format($field_name, '%d/%m/%Y %T') as $alias";
		}
	}
}
