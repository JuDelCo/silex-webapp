<?php

use Src\Frontend\MainProvider;
use Src\Frontend\User\UserProvider;

$app->mount('/',            new MainProvider());
$app->mount('/user',        new UserProvider());
