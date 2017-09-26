<?php

/**
 *  BreweryDB API Token
 */

$app['brewery-api-token'] = '5cdb0593177b100ad0f5178d5d0cc942';


/**
 *  Configuration for the production environment
 */

$app['twig.path']    = array(__DIR__.'/../templates');
$app['twig.options'] = array('cache' => __DIR__.'/../var/cache/twig');

