<?php
namespace GoetasWebservices\SoapServices\SoapClient\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('soap_client');
        $rootNode
            ->children()
                ->arrayNode('alternative_endpoints')->fixXmlConfig('alternative_endpoint')
                    ->prototype('array')
                        ->prototype('scalar')
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('unwrap_returns')
                    ->defaultValue(false)
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
