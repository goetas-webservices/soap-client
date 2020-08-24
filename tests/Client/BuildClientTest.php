<?php

namespace GoetasWebservices\SoapServices\SoapClient\Tests\Client;

use GoetasWebservices\SoapServices\SoapClient\ClientFactory;
use GoetasWebservices\SoapServices\SoapClient\Metadata\Generator\MetadataGenerator;
use GoetasWebservices\SoapServices\SoapClient\Metadata\Loader\DevMetadataLoader;
use GoetasWebservices\WsdlToPhp\Tests\Generator;
use GoetasWebservices\XML\SOAPReader\SoapReader;
use GoetasWebservices\XML\WSDLReader\DefinitionsReader;
use GoetasWebservices\XML\WSDLReader\Exception\PortNotFoundException;
use GoetasWebservices\Xsd\XsdToPhp\Naming\ShortNamingStrategy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class BuildClientTest extends TestCase
{
    /**
     * @var ClientFactory
     */
    protected $factory;

    public function setUp(): void
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
        $client = $this->factory->getClient(__DIR__ . '/../Fixtures/test.wsdl');
        $this->assertNotNull($client);
    }

    public function testGetService()
    {
        $client = $this->factory->getClient(__DIR__ . '/../Fixtures/test.wsdl', 'testSOAP');
        $this->assertNotNull($client);
    }

    public function testGetWrongPort()
    {
        $this->expectException(PortNotFoundException::class);
        $this->expectExceptionMessage("The port named XXX can not be found");
        $client = $this->factory->getClient(__DIR__ . '/../Fixtures/test.wsdl', 'XXX');
        $this->assertNotNull($client);
    }

    public function testGetWrongService()
    {
        $this->expectException(PortNotFoundException::class);
        $this->expectExceptionMessage("The port named testSOAP can not be found");
        $client = $this->factory->getClient(__DIR__ . '/../Fixtures/test.wsdl', 'testSOAP', 'alternativeTest');
        $this->assertNotNull($client);
    }

}
