<?php

use Silex\Application;

class ApiUtilData
{
	// Obtiene un array con los permisos de un usuario
	static function get_user_roles(Application $app, $user_id)
	{
		$date = date('d/m/Y');

		// TODO: Obtener roles de un usuario
		// 		$sql = "value";
		// 		return $app['sql']->getAll($sql);

		return array();
	}

	// Obtiene un array con los permisos del menu de un usuario
	static function get_user_menu_roles(Application $app, $user_id)
	{
		$date = date('d/m/Y');
		
		// TODO: Obtener permisos del menu de un usuario
		// 		$sql = "menu_id, hashtag, menu_parent_id";
		// 		return $app['sql']->getAll($sql);
		
		return array();
	}
}
