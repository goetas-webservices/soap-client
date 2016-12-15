<?php

namespace GoetasWebservices\SoapServices\SoapClient\Tests\Client;

use GoetasWebservices\SoapServices\SoapClient\Builder\SoapContainerBuilder;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testDI()
    {
        $builder = new SoapContainerBuilder(__DIR__ . '/../Fixtures/config.yml');
        $debugContainer = $builder->getDebugContainer();

        $builder->dumpContainerForProd('/tmp', $debugContainer);
    }

}
