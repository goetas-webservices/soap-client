<?php

namespace Example;

use GoetasWebservices\SoapServices\SoapClient\Builder\SoapContainerBuilder;
use GoetasWebservices\SoapServices\SoapClient\ClientFactory;

require __DIR__ . '/vendor/autoload.php';

$containerBuilder = new SoapContainerBuilder('config.yml');

$debug = true;
if ($debug) {
    $container = $containerBuilder->getDebugContainer();

    $containerBuilder->dumpContainerForProd('.', $container);
} else {
    // this will use the pre-generated container that has to be commited
    $container = $containerBuilder->getProdContainer();
}

$serializer = $containerBuilder->createSerializerBuilderFromContainer($container)->build();
$metadata = $container->get('goetas.soap_client.metadata_reader');

$factory = new ClientFactory($metadata, $serializer);

/**
 * @var $client
 */
$client = $factory->getClient('test.wsdl');

$result = $client->getSimple();
