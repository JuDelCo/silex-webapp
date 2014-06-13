<?php

namespace Src\Frontend;

use Src\Lib\Application;

class Main extends Application
{
	function root()
	{
		return $this->renderResponse('info.twig', array(
			'mensaje' => 'Página principal',
			'menu_alt_info_label' => 'Menú principal',
			'titulo' => 'Página Principal'
		));
	}

	function index()
	{
		return $this->redirectRoute('rt_root');
	}

	function main()
	{
		return $this->renderResponse('info.twig', array(
			'mensaje' => 'Página principal (posterior al login)',
			'menu_alt_info_label' => 'Menú principal',
			'titulo' => 'Página Principal'
		));
	}
}
