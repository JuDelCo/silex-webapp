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
	$twig->addGlobal('resource_css_bootstrap_non_responsive',   create_css_tag('bootstrap-non-responsive.css',      '3.1.0_4'));
	$twig->addGlobal('resource_css_bootstrap_theme',            create_css_tag('bootstrap-theme.css',               '3.1.0'));
	$twig->addGlobal('resource_css_bootstrap_dropdown_submenu', create_css_tag('bootstrap-dropdown-submenu.css',    '3.0.1_1'));
	$twig->addGlobal('resource_css_bootstrap_tagsinput',        create_css_tag('bootstrap-tagsinput.css',           '0.3.9'));
	$twig->addGlobal('resource_css_bootstrap_bootstro',         create_css_tag('bootstrap-bootstro.css',            '2014.05.20'));
	$twig->addGlobal('resource_css_bootstrap_datetimepicker',   create_css_tag('bootstrap-datetimepicker.css',      '3.0.0_2'));
	$twig->addGlobal('resource_css_bootstrap_modal',            create_css_tag('bootstrap-modal.css',               '2.2.4_1'));
	$twig->addGlobal('resource_css_font_awesome',               create_css_tag('font-awesome.css',                  '4.0.3'));
	$twig->addGlobal('resource_css_main',                       create_css_tag('main.css',                          '1.0.7'));

	$twig->addGlobal('resource_js_respond',                     create_js_tag('respond.js',                         '1.3.0'));
	$twig->addGlobal('resource_js_moment',                      create_js_tag('moment.js',                          '2.5.1_2'));
	$twig->addGlobal('resource_js_jquery',                      create_js_tag('jquery.js',                          '1.11.0'));
	$twig->addGlobal('resource_js_jquery2',                     create_js_tag('jquery2.js',                         '2.1.0'));
	$twig->addGlobal('resource_js_jquery_ui_custom',            create_js_tag('jquery.ui.custom.js',                '1.10.4'));
	$twig->addGlobal('resource_js_jquery_util',                 create_js_tag('jquery.util.js',                     '1.0.6'));
	$twig->addGlobal('resource_js_jquery_util_form',            create_js_tag('jquery.util.form.js',                '1.0.1'));
	$twig->addGlobal('resource_js_jquery_sha256',               create_js_tag('jquery.sha256.js',                   '1.1.0'));
	$twig->addGlobal('resource_js_jquery_hotkeys',              create_js_tag('jquery.hotkeys.js',                  '0.1.0'));
	$twig->addGlobal('resource_js_jquery_meio_mask',            create_js_tag('jquery.meio-mask.js',                '1.1.11'));
	$twig->addGlobal('resource_js_jquery_stupidtable',          create_js_tag('jquery.stupidtable.js',              '2014.05.12'));
	$twig->addGlobal('resource_js_jquery_filedownload',         create_js_tag('jquery.filedownload.js',             '1.4.2'));
	$twig->addGlobal('resource_js_jquery_fineuploader',         create_js_tag('jquery.fineuploader.js',             '4.4.0'));
	$twig->addGlobal('resource_js_bootstrap',                   create_js_tag('bootstrap.js',                       '3.1.0'));
	$twig->addGlobal('resource_js_bootstrap_tagsinput',         create_js_tag('bootstrap.tagsinput.js',             '0.3.9'));
	$twig->addGlobal('resource_js_bootstrap_bootstro',          create_js_tag('bootstrap.bootstro.js',              '2014.05.20'));
	$twig->addGlobal('resource_js_bootstrap_datetimepicker',    create_js_tag('bootstrap.datetimepicker.js',        '3.0.0_2'));
	$twig->addGlobal('resource_js_bootstrap_modal',             create_js_tag('bootstrap.modal.js',                 '2.2.4'));

	// Funciones Twig ---------------------------

	$twig->addFunction(new \Twig_SimpleFunction('resource_css', function ($lib_path, $version)
	{
		return create_css_tag($lib_path, $version);
	}));

	$twig->addFunction(new \Twig_SimpleFunction('resource_js', function ($lib_path, $version)
	{
		return create_js_tag($lib_path, $version);
	}));

	$twig->addFunction(new \Twig_SimpleFunction('str_pad_left', function($input, $pad_length, $pad_string = '')
	{
		$length_diff = (mb_strlen(utf8_encode($input)) - strlen($input)) / 2;

		return str_pad($input, ($pad_length + $length_diff), $pad_string, STR_PAD_LEFT);
	}));

	$twig->addFunction(new \Twig_SimpleFunction('str_pad_right', function($input, $pad_length, $pad_string = '')
	{
		$length_diff = (mb_strlen(utf8_encode($input)) - strlen($input)) / 2;

		return str_pad($input, ($pad_length + $length_diff), $pad_string, STR_PAD_RIGHT);
	}));

	$twig->addFunction(new \Twig_SimpleFunction('str_pos', function($haystack, $needle, $offset = 0)
	{
		return strpos(utf8_decode($haystack), $needle, $offset);
	}));

	$twig->addFunction(new \Twig_SimpleFunction('str_r_pos', function($haystack, $needle, $offset = 0)
	{
		return strrpos(utf8_decode($haystack), $needle, $offset);
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
