<?php

$app['twig'] = $app->share($app->extend('twig', function($twig, $app)
{
	// Recursos Web -----------------------------
	
	function create_css_tag($lib_path, $version)
	{
		return "<link type='text/css' rel='stylesheet' href='/Resources/css/$lib_path?v=$version' />";
	}

	function create_js_tag($lib_path, $version)
	{
		return "<script type='text/javascript' src='/Resources/js/$lib_path?v=$version'></script>";
	}

	// TODO: Obtener root de app
	/*
	$twig->addFunction(new Twig_SimpleFunction('test_function', function () use ($twig)
	{
		return $twig->getGlobals()['app']['session.version'];
	}));
	*/

	$twig->addGlobal('resource_css_bootstrap',                  create_css_tag('bootstrap.css',                     '3.1.0_1'));
	$twig->addGlobal('resource_css_bootstrap_non_responsive',   create_css_tag('bootstrap-non-responsive.css',      '3.1.0_1'));
	$twig->addGlobal('resource_css_bootstrap_theme',            create_css_tag('bootstrap-theme.css',               '3.1.0'));
	$twig->addGlobal('resource_css_bootstrap_dropdown_submenu', create_css_tag('bootstrap-dropdown-submenu.css',    '3.0.1'));
	$twig->addGlobal('resource_css_bootstrap_datepicker',       create_css_tag('bootstrap-datepicker.css',          '1.3.0-rc.6_1'));
	$twig->addGlobal('resource_css_bootstrap_tagsinput',        create_css_tag('bootstrap-tagsinput.css',           '0.3.9'));
	$twig->addGlobal('resource_css_bootstrap_modal',            create_css_tag('bootstrap-modal.css',               '2.2.4_1'));
	$twig->addGlobal('resource_css_font_awesome',               create_css_tag('font-awesome.css',                  '4.0.3'));
	$twig->addGlobal('resource_css_main',                       create_css_tag('main.css',                          '1.0.3'));

	$twig->addGlobal('resource_js_respond',                     create_js_tag('respond.js',                         '1.3.0'));
	$twig->addGlobal('resource_js_jquery',                      create_js_tag('jquery.js',                          '1.11.0'));
	$twig->addGlobal('resource_js_jquery_util',                 create_js_tag('jquery.util.js',                     '1.0.4'));
	$twig->addGlobal('resource_js_jquery_sha256',               create_js_tag('jquery.sha256.js',                   '1.1.0'));
	$twig->addGlobal('resource_js_jquery_hotkeys',              create_js_tag('jquery.hotkeys.js',                  '0.1.0'));
	$twig->addGlobal('resource_js_jquery_meio_mask',            create_js_tag('jquery.meio-mask.js',                '1.1.11'));
	$twig->addGlobal('resource_js_jquery_stupidtable',          create_js_tag('jquery.stupidtable.js',              '2014.01.21'));
	$twig->addGlobal('resource_js_bootstrap',                   create_js_tag('bootstrap.js',                       '3.1.0'));
	$twig->addGlobal('resource_js_bootstrap_datepicker',        create_js_tag('bootstrap.datepicker.js',            '1.3.0-rc.6'));
	$twig->addGlobal('resource_js_bootstrap_tagsinput',         create_js_tag('bootstrap.tagsinput.js',             '0.3.9'));
	$twig->addGlobal('resource_js_bootstrap_modal',             create_js_tag('bootstrap.modal.js',                 '2.2.4'));

	// Funciones Twig ---------------------------

	$twig->addFunction(new Twig_SimpleFunction('resource_css', function ($lib_path, $version)
	{
		return create_css_tag($lib_path, $version);
	}));
	
	$twig->addFunction(new Twig_SimpleFunction('resource_js', function ($lib_path, $version)
	{
		return create_js_tag($lib_path, $version);
	}));
	
	$twig->addFunction(new \Twig_SimpleFunction('util_calc_size', function ($size)
	{
		$size = intval($size);

		if($size < 1024)
		{
			$size = $size . ' bytes';
		}
		elseif($size < (1024 * 1024))
		{
			$size = number_format(($size / 1024), 1, '.', "") . ' KB';
		}
		else
		{
			$size = number_format(($size / (1024 * 1024)), 1, '.', "") . ' MB';
		}

		return $size;
	}));

	return $twig;
}));
