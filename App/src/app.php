<?php

use Silex\Application;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\SwiftmailerServiceProvider;

// Docs: http://symfony.com/components

$app = new Application();

$app->register(new UrlGeneratorServiceProvider());
$app->register(new DoctrineServiceProvider());
$app->register(new SessionServiceProvider());
$app->register(new TranslationServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new SwiftmailerServiceProvider());

require_once __DIR__.'/lib/util_provider.php';

$app->register(new SqlHelperUtilServiceProvider());
$app->register(new ValidatorHelperUtilServiceProvider());
$app->register(new AppHelperUtilServiceProvider());
$app->register(new AuthHelperUtilServiceProvider());
$app->register(new UploadHelperUtilServiceProvider());
$app->register(new ExcelHelperUtilServiceProvider());
$app->register(new EmailHelperUtilServiceProvider());

return $app;
