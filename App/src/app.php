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

require_once __DIR__.'/lib/__services.php';

$app->register(new SqlHelperServiceProvider());
$app->register(new ValidatorHelperServiceProvider());
$app->register(new AppHelperServiceProvider());
$app->register(new AuthHelperServiceProvider());
$app->register(new UploadHelperServiceProvider());
$app->register(new ExcelHelperServiceProvider());
$app->register(new EmailHelperServiceProvider());

return $app;
