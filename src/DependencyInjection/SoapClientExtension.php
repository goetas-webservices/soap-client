<?php
namespace GoetasWebservices\SoapServices\SoapClient\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class SoapClientExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $xml = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $xml->load('services.xml');

        $config = $this->processConfiguration(new Configuration(), $configs);
        foreach ($configs as $subConfig) {
            $config = array_merge($config, $subConfig);
        }
        $container->setParameter('goetas.soap_client.config', $config);
    }

    protected static function sanitizePhp($ns)
    {
        return strtr($ns, '/', '\\');
    }

    public function getAlias()
    {
        return 'soap_client';
    }
}
