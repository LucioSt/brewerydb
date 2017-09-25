<?php

namespace Brewerydb;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RouteCollection;
use Silex\Application;

ini_set('display_errors', 1);

$filename = __DIR__.preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
}

require_once __DIR__.'/../vendor/autoload.php';
require      __DIR__.'/../src/controllers.php';

$app = require __DIR__.'/../src/app.php';
require __DIR__.'/../config/prod.php';

$app['debug'] = true;

$app->run();
