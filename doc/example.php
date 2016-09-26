<?php

namespace Example;

use GlobalWeather\Container\SoapClientContainer;
use GoetasWebservices\SoapServices\SoapClient\Builder\SoapContainerBuilder;
use GoetasWebservices\SoapServices\SoapClient\ClientFactory;

require __DIR__ . '/vendor/autoload.php';

$container = new SoapClientContainer();

$serializer = SoapContainerBuilder::createSerializerBuilderFromContainer($container)->build();
$metadata = $container->get('goetas_webservices.soap_client.metadata_reader');

$factory = new ClientFactory($metadata, $serializer);

/**
 * @var $client \GlobalWeather\SoapStubs\MortgageSoap
 */
$client = $factory->getClient('http://www.webservicex.net/mortgage.asmx?WSDL');

$cities = $client->getMortgagePayment($years=2010, $interest=5.5, $loanAmount=100000.6, $annualTax=5, $annualInsurance=2);

var_dump($cities);
exit;
$result = $client->getWeather("Dresden", "Germany");

var_dump($result);
