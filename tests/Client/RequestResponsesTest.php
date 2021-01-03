<?php

declare(strict_types=1);

namespace GoetasWebservices\SoapServices\SoapClient\Tests\Client;

use GoetasWebservices\SoapServices\Metadata\Envelope\SoapEnvelope12\Messages\Fault;
use GoetasWebservices\SoapServices\Metadata\Generator\MetadataGenerator;
use GoetasWebservices\SoapServices\Metadata\Headers\Handler\HeaderHandler;
use GoetasWebservices\SoapServices\Metadata\Loader\DevMetadataLoader;
use GoetasWebservices\SoapServices\SoapClient\Client;
use GoetasWebservices\SoapServices\SoapClient\ClientFactory;
use GoetasWebservices\SoapServices\SoapClient\Exception\ClientException;
use GoetasWebservices\SoapServices\SoapClient\Exception\UnexpectedFormatException;
use GoetasWebservices\WsdlToPhp\Tests\Generator;
use GoetasWebservices\XML\SOAPReader\SoapReader;
use GoetasWebservices\XML\WSDLReader\DefinitionsReader;
use GoetasWebservices\Xsd\XsdToPhp\Naming\ShortNamingStrategy;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use JMS\Serializer\EventDispatcher\EventDispatcherInterface;
use JMS\Serializer\Handler\HandlerRegistryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

abstract class RequestResponsesTest extends TestCase
{
    /**
     * @var string[]
     */
    protected static $namespaces = ['http://www.example.org/test/' => 'Ex'];
    /**
     * @var Generator
     */
    protected static $generator;

    /**
     * @var MockHandler
     */
    protected $responseMock;

    /**
     * @var array
     */
    protected $requestResponseStack = [];

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ClientFactory
     */
    protected $factory;

    public static function setUpBeforeClass(): void
    {
        self::$generator = new Generator(self::$namespaces, [], __DIR__ . '/tmp');
        self::$generator->generate([__DIR__ . '/../Fixtures/test.wsdl']);
        self::$generator->registerAutoloader();
    }

    public static function tearDownAfterClass(): void
    {
        self::$generator->unRegisterAutoloader();
        //self::$generator->cleanDirectories();
    }

    public function setUp(): void
    {
        $ref = new \ReflectionClass(Fault::class);

        $headerHandler = new HeaderHandler();

        $listeners = static function (EventDispatcherInterface $d) use ($headerHandler): void {
            $d->addSubscriber($headerHandler);
        };

        $handlers = static function (HandlerRegistryInterface $h) use ($headerHandler): void {
            $h->registerSubscribingHandler($headerHandler);
        };

        $serializer = self::$generator->buildSerializer($handlers, [
            'GoetasWebservices\SoapServices\Metadata\Envelope\SoapEnvelope12' => dirname($ref->getFileName()) . '/../../../Resources/metadata/jms12',
            'GoetasWebservices\SoapServices\Metadata\Envelope\SoapEnvelope' => dirname($ref->getFileName()) . '/../../../Resources/metadata/jms',
        ], $listeners);

        $this->responseMock = new MockHandler();
        $history = Middleware::history($this->requestResponseStack);

        $handler = HandlerStack::create($this->responseMock);
        $handler->push($history);

        $guzzle = new GuzzleHttpClient(['handler' => $handler]);

        $naming = new ShortNamingStrategy();
        $dispatcher = new EventDispatcher();
        $wsdlReader = new DefinitionsReader(null, $dispatcher);
        $soapReader = new SoapReader();
        $dispatcher->addSubscriber($soapReader);

        $metadataGenerator = new MetadataGenerator($naming, self::$namespaces);
        $metadataLoader = new DevMetadataLoader($metadataGenerator, $soapReader, $wsdlReader);

        $this->factory = new ClientFactory($metadataLoader, $serializer);
        $this->factory->setHttpClient($guzzle);
        $this->client = $this->getClient();
    }

    abstract protected function getClient(): Client;

    public function getErrorResponses(): array
    {
        return [
            [new Response(500, ['Content-Type' => 'text/xml'], '<foo/>')],
            [new Response(500, ['Content-Type' => 'text/html'])],

            [new Response(404, ['Content-Type' => 'text/xml'], '<foo/>')],
            [new Response(404, ['Content-Type' => 'text/html'])],

            [new Response(200, ['Content-Type' => 'text/html'])],

            [new Response(500, ['Content-Type' => 'application/soap+xml'], '<foo/>')],
            [new Response(500, ['Content-Type' => 'text/html'])],

            [new Response(404, ['Content-Type' => 'application/soap+xml'], '<foo/>')],
            [new Response(404, ['Content-Type' => 'text/html'])],

            [new Response(200, ['Content-Type' => 'text/html'])],
        ];
    }

    public function getHttpFaultCodes(): array
    {
        return [
            [500],
            [200],
        ];
    }

    /**
     * @dataProvider getErrorResponses
     */
    public function testGetSimpleError(ResponseInterface $response): void
    {
        $this->expectException(UnexpectedFormatException::class);
        $this->responseMock->append($response);

        $this->client->getSimple('foo');
    }

    public function testNoMethod(): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('Can not find an operation to run abc service call');

        $this->client->abc();
    }
}
