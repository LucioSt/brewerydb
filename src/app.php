<?php

namespace Brewerydb;

use Silex\Application;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\CsrfServiceProvider;

$app = new Application();
$app->register(new ServiceControllerServiceProvider());
$app->register(new AssetServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new HttpFragmentServiceProvider());
$app->register(new CsrfServiceProvider());
$app['twig'] = $app->extend('twig', function ($twig, $app) {
    return $twig;
});

$app->mount('', new ControllerProvider());

return $app;

