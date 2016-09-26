<?php
namespace GoetasWebservices\SoapServices\SoapClient\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ConfigurePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $soapConfig = $container->getParameter('goetas_webservices.soap_client.config');
        $xsd2phpConfig = $container->getParameter('goetas_webservices.xsd2php.config');
        $wsdlConfig = $container->getParameter('goetas_webservices.wsdl2php.config');

        $metadataGenerator = $container->getDefinition('goetas_webservices.soap_common.metadata.generator');
        foreach ($soapConfig['alternative_endpoints'] as $service => $data) {
            foreach ($data as $port => $endPoint) {
                $metadataGenerator->addMethodCall('addAlternativeEndpoint', [$service, $port, $endPoint]);
            }
        }
        $keys = ['headers', 'parts', 'messages'];
        $metadataGenerator->addMethodCall('setBaseNs', [array_intersect_key($wsdlConfig, array_combine($keys, $keys))]);
        $metadataGenerator->addMethodCall('setUnwrap', [$soapConfig['unwrap_returns']]);
        $metadataGenerator->replaceArgument(1, $xsd2phpConfig['namespaces']);

        $writer = $container->getDefinition('goetas_webservices.soap_client.stub.client_generator');
        $writer->addMethodCall('setUnwrap', [$soapConfig['unwrap_returns']]);


        $forProduction = !!$container->getParameter('goetas_webservices.soap_common.metadata');

        $readerName = 'goetas_webservices.soap_common.metadata_loader.' . ($forProduction ? 'array' : 'dev');
        $container->setAlias('goetas_webservices.soap_client.metadata_reader', $readerName);
    }
}
