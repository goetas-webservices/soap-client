<?php

declare(strict_types=1);

namespace GoetasWebservices\SoapServices\SoapClient\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CleanupPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // if there are no metadata, then we are in debug mode, no need to clean up the container
        if (!$container->getParameter('goetas_webservices.soap_client.metadata')) {
            return;
        }

        foreach ($container->getDefinitions() as $id => $definition) {
            if (false === strpos($id, 'goetas_webservices.soap_client.metadata_loader.array') && !$definition->isSynthetic() && $definition->isPublic()) {
                $definition->setPublic(false);
            }
        }
    }
}
