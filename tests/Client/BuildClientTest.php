<?php

namespace GoetasWebservices\SoapServices\Tests;

use GoetasWebservices\SoapServices\SoapClient\ClientFactory;
use GoetasWebservices\SoapServices\SoapCommon\MetadataGenerator\MetadataGenerator;
use GoetasWebservices\SoapServices\SoapCommon\MetadataLoader\DevMetadataLoader;
use GoetasWebservices\WsdlToPhp\Tests\Generator;
use GoetasWebservices\XML\SOAPReader\SoapReader;
use GoetasWebservices\XML\WSDLReader\DefinitionsReader;
use GoetasWebservices\Xsd\XsdToPhp\Naming\ShortNamingStrategy;
use Symfony\Component\EventDispatcher\EventDispatcher;

class BuildClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClientFactory
     */
    protected $factory;

    public function setUp()
    {
        $namespaces = [
            'http://www.example.org/test/' => "Ex"
        ];
        $generator = new Generator($namespaces);
        $serializer = $generator->buildSerializer();

        $naming = new ShortNamingStrategy();
        $metadataGenerator = new MetadataGenerator($naming, $namespaces);

        $dispatcher = new EventDispatcher();
        $wsdlReader = new DefinitionsReader(null, $dispatcher);
        $soapReader = new SoapReader();
        $dispatcher->addSubscriber($soapReader);

        $metadataLoader = new DevMetadataLoader($metadataGenerator, $soapReader, $wsdlReader);
        $this->factory = new ClientFactory($metadataLoader, $serializer);
    }

    public function testBuildServer()
    {
        $this->factory->getClient(__DIR__ . '/../Fixtures/test.wsdl');
    }

    public function testGetService()
    {
        $this->factory->getClient(__DIR__ . '/../Fixtures/test.wsdl', 'testSOAP');
    }

    /**
     * @expectedException  \GoetasWebservices\XML\WSDLReader\Exception\PortNotFoundException
     * @expectedExceptionMessage The port named XXX can not be found
     */
    public function testGetWrongPort()
    {
        $this->factory->getClient(__DIR__ . '/../Fixtures/test.wsdl', 'XXX');
    }

    /**
     * @expectedException  \GoetasWebservices\XML\WSDLReader\Exception\PortNotFoundException
     * @expectedExceptionMessage The port named testSOAP can not be found
     */
    public function testGetWrongService()
    {
        $this->factory->getClient(__DIR__ . '/../Fixtures/test.wsdl', 'testSOAP', 'alternativeTest');
    }

}
