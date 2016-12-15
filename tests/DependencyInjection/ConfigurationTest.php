<?php

namespace GoetasWebservices\SoapServices\SoapClient\Tests\Client;

use GoetasWebservices\SoapServices\SoapClient\ClientFactory;
use GoetasWebservices\SoapServices\SoapClient\DependencyInjection\Compiler\CleanupPass;
use GoetasWebservices\SoapServices\SoapClient\DependencyInjection\Configuration;
use GoetasWebservices\SoapServices\SoapClient\DependencyInjection\SoapClientExtension;
use GoetasWebservices\SoapServices\SoapClient\Metadata\Generator\MetadataGenerator;
use GoetasWebservices\SoapServices\SoapClient\Metadata\Loader\DevMetadataLoader;
use GoetasWebservices\WsdlToPhp\Tests\Generator;
use GoetasWebservices\XML\SOAPReader\SoapReader;
use GoetasWebservices\XML\WSDLReader\DefinitionsReader;
use GoetasWebservices\Xsd\XsdToPhp\Naming\ShortNamingStrategy;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{

    public function getMetadataStubs()
    {
        return [
            [[]],
            [['aa']],
        ];
    }
    /**
     * @dataProvider getMetadataStubs
     * @param $metadata
     */
    public function testDI($metadata)
    {
        $container = new ContainerBuilder();
        $container->registerExtension(new SoapClientExtension());
        $container->addCompilerPass(new CleanupPass());

        $container->setParameter('goetas_webservices.soap_client.metadata', $metadata);

        $yamlLoader = new YamlFileLoader($container, new FileLocator([]));
        $yamlLoader->load(__DIR__.'/../Fixtures/config.yml');

        $container->compile();
    }

}
