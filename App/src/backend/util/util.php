<?php

require_once __DIR__.'/util_controller.php';

$util_controller = $app['controllers_factory'];

$util_controller->post('/ajax/', 'ApiUtilController::ajax_data')
->bind('rta_util_ajax');

return $util_controller;
