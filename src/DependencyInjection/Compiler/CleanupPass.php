<?php
namespace GoetasWebservices\SoapServices\SoapClient\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CleanupPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->getParameter('goetas.soap_client.metadata')) {
            return;
        }
        foreach ($container->getDefinitions() as $id => $definition) {
            if (strpos($id, 'goetas.soap_client.metadata_reader') === false && !$definition->isSynthetic()) {
                $definition->setPublic(false);
            }
        }
    }
}
