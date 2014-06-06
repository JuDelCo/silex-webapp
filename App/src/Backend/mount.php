<?php

use Src\Backend\Util\ApiUtilProvider;
use Src\Backend\User\ApiUserProvider;

$app->mount('api/util',      new ApiUtilProvider());
$app->mount('api/user',      new ApiUserProvider());
