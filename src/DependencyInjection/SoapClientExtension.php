<?php
namespace GoetasWebservices\SoapServices\SoapClient\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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
        $container->prependExtensionConfig('goetas_soap_common', []);
    }
}
