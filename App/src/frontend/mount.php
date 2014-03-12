<?php

$app->mount('/',               include('main_routes.php'));
$app->mount('/user',           include('user/routes.php'));
