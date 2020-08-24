<?php

namespace GoetasWebservices\SoapServices\SoapClient\Tests\Stub;

use GoetasWebservices\SoapServices\SoapClient\Builder\SoapContainerBuilder;
use PHPUnit\Framework\TestCase;

class StubGeneratorTest extends TestCase
{
    public function testDI()
    {
        $builder = new SoapContainerBuilder(__DIR__ . '/../Fixtures/config.yml');
        $debugContainer = $builder->getDebugContainer();

        /**
         * @var $clientStubGenerator \GoetasWebservices\SoapServices\SoapClient\StubGeneration\ClientStubGenerator
         */
        $clientStubGenerator = $debugContainer->get('goetas_webservices.soap_client.stub.client_generator');

        $wsdlMetadata = $debugContainer->getParameter('goetas_webservices.soap_client.config')['metadata'];
        $schemas = [];
        $portTypes = [];
        $wsdlReader = $debugContainer->get('goetas_webservices.wsdl2php.wsdl_reader');

        foreach (array_keys($wsdlMetadata) as $src) {
            $definitions = $wsdlReader->readFile($src);
            $schemas[] = $definitions->getSchema();
            $portTypes = array_merge($portTypes, $definitions->getAllPortTypes());
        }
        $classDefinitions = $clientStubGenerator->generate($portTypes);

        $this->assertCount(2, $classDefinitions);


    }

}
