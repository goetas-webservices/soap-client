<?php
namespace GoetasWebservices\SoapServices\SoapClient\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ConfigurePass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        $soapConfig = $container->getParameter('goetas.soap_client.config');
        $wsdlConfig = $container->getParameter('wsdl2php.config');
        $metadataGenerator = $container->getDefinition('goetas.wsdl2php.metadata.generator');
        foreach ($soapConfig['alternative_endpoints'] as $service => $data) {
            foreach ($data as $port => $endPoint) {
                $metadataGenerator->addMethodCall('addAlternativeEndpoint', [$service, $port, $endPoint]);
            }
        }

        $writer = $container->getDefinition('goetas.wsdl2php.metadata.generator');
        $keys = ['headers', 'parts', 'messages'];
        $writer->addMethodCall('setBaseNs', [array_intersect_key($wsdlConfig, array_combine($keys, $keys))]);
        $writer->addMethodCall('setUnwrap', [$soapConfig['unwrap_returns']]);

        $writer = $container->getDefinition('goetas.wsdl2php.stub.client_generator');
        $writer->addMethodCall('setUnwrap', [$soapConfig['unwrap_returns']]);


        $forProduction = !!$container->getParameter('goetas.soap_client.metadata');

        $readerName = 'goetas.wsdl2php.metadata_loader.' . ($forProduction ? 'array' : 'dev');
        $reader = clone $container->getDefinition($readerName);
        $container->setDefinition('goetas.soap_client.metadata_reader', $reader);
    }
}
