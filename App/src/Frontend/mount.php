<?php

$app->mount('/',               include('main_routes.php'));
$app->mount('/user',           include('User/routes.php'));
