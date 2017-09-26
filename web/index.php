<?php
/**
 * Created by PhpStorm.
 * User: lucio
 * Date: 25/09/17
 * Time: 9:12
 *
 * Index
 *
 * @author Lucio Stocco
 *
 */

namespace Brewerydb;

ini_set('display_errors', 1);

$filename = __DIR__.preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);

if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
}

// Define Auto-load
$loader = require_once    __DIR__.'/../vendor/autoload.php';

// Register my class in Auto-load
$loader->add('Brewerydb', dirname(__DIR__).'/src/');
$loader->add('Brewerydb\Pintlabs', dirname(__DIR__).'/src/Pintlabs/');

require      __DIR__.'/../src/Pintlabs/Service/Brewerydb/Exception.php';
require      __DIR__.'/../src/Pintlabs/Service/Brewerydb.php';

$app = require __DIR__.'/../src/app.php';
require __DIR__.'/../config/prod.php';

$app['debug'] = true;

$app->run();
