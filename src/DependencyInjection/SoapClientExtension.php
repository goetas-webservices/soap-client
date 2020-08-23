<?php

namespace GoetasWebservices\SoapServices\SoapClient\DependencyInjection;

use Psr\Log\NullLogger;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class SoapClientExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        foreach ($configs as $subConfig) {
            $config = array_merge($config, $subConfig);
        }

        $container->setParameter('goetas_webservices.soap_client.config', $config);
        $container->setParameter('goetas_webservices.soap_client.unwrap_returns', $config['unwrap_returns']);

        $xml = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $xml->load('services.xml');


        $container->setDefinition('logger', new Definition(NullLogger::class));

        $definition = $container->getDefinition('goetas_webservices.xsd2php.path_generator.jms.' . $config['path_generator']);
        $container->setDefinition('goetas_webservices.xsd2php.path_generator.jms', clone $definition);

        $definition = $container->getDefinition('goetas_webservices.xsd2php.path_generator.php.' . $config['path_generator']);
        $container->setDefinition('goetas_webservices.xsd2php.path_generator.php', clone $definition);


        $pathGenerator = $container->getDefinition('goetas_webservices.xsd2php.path_generator.jms');
        $pathGenerator->addMethodCall('setTargets', [$config['destinations_jms']]);

        $pathGenerator = $container->getDefinition('goetas_webservices.xsd2php.path_generator.php');
        $pathGenerator->addMethodCall('setTargets', [$config['destinations_php']]);


        foreach (['php', 'jms'] as $type) {
            $converter = $container->getDefinition('goetas_webservices.xsd2php.converter.' . $type);
            foreach ($config['namespaces'] as $xml => $php) {
                $converter->addMethodCall('addNamespace', [$xml, self::sanitizePhp($php)]);
            }
            foreach ($config['aliases'] as $xml => $data) {
                foreach ($data as $type => $php) {
                    $converter->addMethodCall('addAliasMapType', [$xml, $type, self::sanitizePhp($php)]);
                }
            }
        }


        $definition = $container->getDefinition('goetas_webservices.xsd2php.naming_convention.' . $config['naming_strategy']);
        $container->setDefinition('goetas_webservices.xsd2php.naming_convention', $definition);

//////////

        $metadataGenerator = $container->getDefinition('goetas_webservices.soap_client.metadata.generator');
        foreach ($config['alternative_endpoints'] as $service => $data) {
            foreach ($data as $port => $endPoint) {
                $metadataGenerator->addMethodCall('addAlternativeEndpoint', [$service, $port, $endPoint]);
            }
        }
        $keys = ['headers', 'parts', 'messages'];
        //$metadataGenerator->addMethodCall('setBaseNs', [array_intersect_key($config, array_combine($keys, $keys))]);
        $metadataGenerator->addMethodCall('setUnwrap', [$config['unwrap_returns']]);
        $metadataGenerator->replaceArgument(1, $config['namespaces']);

        $writer = $container->getDefinition('goetas_webservices.soap_client.stub.client_generator');
        $writer->addMethodCall('setUnwrap', [$config['unwrap_returns']]);


        $forProduction = !!$container->getParameter('goetas_webservices.soap_client.metadata');

        $readerName = 'goetas_webservices.soap_client.metadata_loader.' . ($forProduction ? 'array' : 'dev');
        $alias = $container->setAlias('goetas_webservices.soap_client.metadata_reader', $readerName);
        if ($alias instanceof Alias) {
            $alias->setPublic(true);
        }
    }

    protected static function sanitizePhp($ns)
    {
        return strtr($ns, '/', '\\');
    }

    public function getAlias()
    {
        return 'soap_client';
    }

    /**
     * Allow an extension to prepend the extension configurations.
     *
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('goetas_soap_client', []);
    }
}
