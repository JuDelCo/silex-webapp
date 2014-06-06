<?php

$app->mount('api/util',        include('Util/routes.php'));
$app->mount('api/user',        include('User/routes.php'));
