<?php

namespace Brewerydb;

use Silex\Application;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\CsrfServiceProvider;
use KuiKui\MemcacheServiceProvider;

$app = new Application();
$app->register(new ServiceControllerServiceProvider());
$app->register(new AssetServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new HttpFragmentServiceProvider());
$app->register(new CsrfServiceProvider());
$app->register(new MemcacheServiceProvider\ServiceProvider());
$app['twig'] = $app->extend('twig', function ($twig, $app) {
    return $twig;
});

$app->mount('', new ControllerProvider());

// Memcached Configrations ************
$app['memcache.default_duration'] = 86400;  // 1 day
$app['memcache.class'] = '\Memcached';      //

return $app;

