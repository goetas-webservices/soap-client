<?php

namespace GoetasWebservices\SoapServices\SoapClient\Tests\Client;

use GoetasWebservices\SoapServices\SoapClient\ClientFactory;
use GoetasWebservices\SoapServices\SoapClient\Metadata\Generator\MetadataGenerator;
use GoetasWebservices\SoapServices\SoapClient\Metadata\Loader\DevMetadataLoader;
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

    protected static $namespaces = [
        'http://www.example.org/test/' => "Ex",
        'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd' => 'GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Secext',
        'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd' => 'GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Utility',
        'http://www.w3.org/2000/09/xmldsig#' => 'GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign',
    ];

    public function setUp()
    {
        $generator = new Generator(self::$namespaces);
        $serializer = $generator->buildSerializer();

        $naming = new ShortNamingStrategy();
        $metadataGenerator = new MetadataGenerator($naming, self::$namespaces);

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
