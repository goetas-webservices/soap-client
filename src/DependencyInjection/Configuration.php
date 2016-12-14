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





                ->scalarNode('naming_strategy')
                    ->defaultValue('short')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('path_generator')
                    ->defaultValue('psr4')
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('namespaces')->fixXmlConfig('namespace')
                    ->cannotBeEmpty()->isRequired()
                    ->requiresAtLeastOneElement()
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->arrayNode('known_locations')->fixXmlConfig('known_location')
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->arrayNode('destinations_php')->fixXmlConfig('destination_php')
                    ->cannotBeEmpty()->isRequired()
                    ->requiresAtLeastOneElement()
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->arrayNode('destinations_jms')->fixXmlConfig('destination_jms')
                    ->cannotBeEmpty()->isRequired()
                    ->requiresAtLeastOneElement()
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->arrayNode('aliases')->fixXmlConfig('alias')
                    ->prototype('array')
                        ->prototype('scalar')
                        ->end()
                    ->end()
                ->end()



                ->scalarNode('headers')
                    ->defaultValue('\\SoapEnvelope\\Headers')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('parts')
                    ->defaultValue('\\SoapEnvelope\\Parts')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('messages')
                    ->defaultValue('\\SoapEnvelope\\Messages')
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('metadata')->fixXmlConfig('metadata')
                    ->cannotBeEmpty()->isRequired()
                    ->requiresAtLeastOneElement()
                    ->prototype('scalar')->end()
                ->end()





            ->end()
        ;
        return $treeBuilder;
    }
}
