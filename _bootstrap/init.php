<?php

/*
|--------------------------------------------------------------------------
| Initialising a new Kabas instance
|--------------------------------------------------------------------------
|
| Using the main class
|
*/

$app = new Kabas\App();

$app->boot();
$app->loadAliases();
$app->loadTheme();
$app->react();

return $app;
