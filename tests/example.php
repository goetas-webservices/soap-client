<?php
namespace Example;

/**
 * @var $loader \Composer\Autoload\ClassLoader
 */
use GoetasWebservices\SoapServices\SoapClient\Builder\SoapContainerBuilder;
use GoetasWebservices\SoapServices\SoapClient\ClientFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use TestNs\Container\SoapClientContainer;
use TestNs\GetSimpleResponse;

$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->addPsr4('TestNs\\', __DIR__ . '/../soap/src');

$container = new SoapClientContainer();

$serializer = SoapContainerBuilder::createSerializerBuilderFromContainer($container)->build();
$metadata = $container->get('goetas_webservices.soap_client.metadata_reader');

$responseMock = new MockHandler();
$httpResponse = new Response(200, ['Content-Type' => 'text/xml'], '
        <SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/">
          <SOAP:Body xmlns:ns-b3c6b39d="http://www.example.org/test/">
            <ns-b3c6b39d:getSimpleResponse xmlns:ns-b3c6b39d="http://www.example.org/test/">
              <out><![CDATA[A]]></out>
            </ns-b3c6b39d:getSimpleResponse>
          </SOAP:Body>
        </SOAP:Envelope>');

$responseMock->append($httpResponse);

$requestResponseStack = [];
$history = Middleware::history($requestResponseStack);

$handler = HandlerStack::create($responseMock);
$handler->push($history);

$guzzle = new Client(['handler' => $handler]);

$factory = new ClientFactory($metadata, $serializer);
$factory->setHttpClient(new GuzzleAdapter($guzzle));

/**
 * @var $client \TestNs\SoapStubs\Test
 */
$client = $factory->getClient('tests/Fixtures/test.wsdl');

$out = $client->getSimple("bar");

if (!($out instanceof GetSimpleResponse)) {
    echo "\$out is not instanceof GetSimpleResponse\n";
    exit(-128);
}
$request = (string)$requestResponseStack[0]['request']->getBody();
if (strpos($request, '<in><![CDATA[bar]]></in>') === false || strpos($request, ':getSimple ') === false) {
    echo "Wrong request\n";
    exit(-128);
}

exit(0);
