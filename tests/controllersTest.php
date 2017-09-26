<?php

namespace Brewerydb;

$loader = require __DIR__ . '/../vendor/autoload.php';
require __DIR__.'/../src/Brewerydb/Validation.php';

use Silex\WebTestCase;


class controllersTest extends WebTestCase
{
    public function testValidation()
    {

        $this->assertEquals(true, Validation::textValidation("ASD-asaas 123 -"));
        $this->assertEquals(false, Validation::textValidation("(#)"));
        $this->assertEquals(false, Validation::textValidation("<br>"));

    }

    public function createApplication()
    {
        $app['session.test'] = true;

        return;
    }
}
