<?php

namespace GoetasWebservices\SoapServices\Tests;

use GoetasWebservices\SoapServices\SoapClient\ClientFactory;
use GoetasWebservices\SoapServices\SoapClient\Exception\FaultException;
use GoetasWebservices\SoapServices\SoapCommon\Metadata\PhpMetadataGenerator;
use GoetasWebservices\SoapServices\SoapCommon\SoapEnvelope\Parts\Fault;
use GoetasWebservices\WsdlToPhp\Tests\Generator;
use GuzzleHttp\Client;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class ClientRequestResponsesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Generator
     */
    protected static $generator;
    /**
     * @var Server
     */
    protected static $server;

    /**
     * @var \GuzzleHttp\Handler\MockHandler
     */
    protected $responseMock;

    protected $requestResponseStack = [];

    public static function setUpBeforeClass()
    {
        $namespaces = [
            'http://www.example.org/test/' => "Ex"
        ];

        self::$generator = new Generator($namespaces);
        self::$generator->generate([__DIR__ . '/../Fixtures/Soap/test.wsdl']);
        self::$generator->registerAutoloader();
    }

    public static function tearDownAfterClass()
    {
        self::$generator->unRegisterAutoloader();
        //self::$generator->cleanDirectories();
    }

    public function setUp()
    {
        $namespaces = [
            'http://www.example.org/test/' => "Ex"
        ];
        $generator = new Generator($namespaces);
        $ref = new \ReflectionClass(Fault::class);
        $serializer = $generator->buildSerializer(null, [
            'GoetasWebservices\SoapServices\SoapCommon\SoapEnvelope' => dirname($ref->getFileName()) . '/../../Resources/metadata/jms'
        ]);

        $this->responseMock = new MockHandler();
        $history = Middleware::history($this->requestResponseStack);

        $handler = HandlerStack::create($this->responseMock);
        $handler->push($history);

        $guzzle = new Client(['handler' => $handler]);

        $this->factory = new ClientFactory($namespaces, $serializer);
        $this->factory->setHttpClient(new GuzzleAdapter($guzzle));

        $metadataGenerator = new PhpMetadataGenerator($namespaces);
        $this->factory->setMetadataGenerator($metadataGenerator);
    }

    public function testGetSimple()
    {
        $httpResponse = new Response(200, ['Content-Type' => 'text/xml'], '
        <SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/">
          <SOAP:Body xmlns:ns-b3c6b39d="http://www.example.org/test/">
            <ns-b3c6b39d:getSimpleResponse xmlns:ns-b3c6b39d="http://www.example.org/test/">
              <out><![CDATA[A]]></out>
            </ns-b3c6b39d:getSimpleResponse>
          </SOAP:Body>
        </SOAP:Envelope>');

        $this->responseMock->append($httpResponse);
        $client = $this->factory->getClient(__DIR__ . '/../Fixtures/Soap/test.wsdl', null, null, true);

        $response = $client->getSimple("foo");
        $this->assertEquals("A", $response);

        $this->responseMock->append($httpResponse);
        $client = $this->factory->getClient(__DIR__ . '/../Fixtures/Soap/test.wsdl', null, null, false);

        /**
         * @var $response \Ex\GetSimpleResponse
         */
        $response = $client->getSimple("foo");
        $this->assertInstanceOf('Ex\GetSimpleResponse', $response);
        $this->assertEquals("A", $response->getOut());
    }

    public function testNoOutput()
    {
        $this->responseMock->append(
            new Response(200, ['Content-Type' => 'text/xml'], '
            <SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/">
              <SOAP:Body />
            </SOAP:Envelope>')
        );
        $client = $this->factory->getClient(__DIR__ . '/../Fixtures/Soap/test.wsdl', null, null, true);

        $response = $client->noOutput("foo");
        $this->assertNull($response);
    }

    public function testNoInput()
    {
        $this->responseMock->append(
            new Response(200, ['Content-Type' => 'text/xml'], '
            <SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/">
              <SOAP:Body xmlns:ns-b3c6b39d="http://www.example.org/test/">
                <ns-b3c6b39d:noInputResponse xmlns:ns-b3c6b39d="http://www.example.org/test/">
                  <out><![CDATA[A]]></out>
                </ns-b3c6b39d:noInputResponse>
              </SOAP:Body>
            </SOAP:Envelope>')
        );
        $client = $this->factory->getClient(__DIR__ . '/../Fixtures/Soap/test.wsdl', null, null, true);

        $client->noInput("foo");
        $this->assertTrue(
            strpos(
                $this->requestResponseStack[0]['request']->getBody(),
                '<SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/"/>'
            ) !== false
        );
    }

    public function testNoBoth()
    {
        $this->responseMock->append(
            new Response(
                200,
                ['Content-Type' => 'text/xml'],
                '<SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/"/>'
            )
        );
        $client = $this->factory->getClient(__DIR__ . '/../Fixtures/Soap/test.wsdl', null, null, true);

        $response = $client->noBoth("foo");
        $this->assertNull($response);
        $this->assertTrue(
            strpos(
                $this->requestResponseStack[0]['request']->getBody(),
                '<SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/"/>'
            ) !== false
        );
    }


    public function getErrorResponses()
    {
        return [
            [new Response(500, ['Content-Type' => 'text/xml'], '<foo/>')],
            [new Response(500, ['Content-Type' => 'text/html'])],
            [new Response(404, ['Content-Type' => 'text/xml'], '<foo/>')],
            [new Response(404, ['Content-Type' => 'text/html'])],
            [new Response(200, ['Content-Type' => 'text/html'])],
        ];
    }

    /**
     * @expectedException \Exception
     * @dataProvider getErrorResponses
     * @param ResponseInterface $response
     */
    public function testGetSimpleError(ResponseInterface $response)
    {
        $this->responseMock->append($response);
        $client = $this->factory->getClient(__DIR__ . '/../Fixtures/Soap/test.wsdl');
        $client->getSimple("foo");
    }

    public function testApplicationError()
    {
        $this->responseMock->append(
            new Response(500, ['Content-Type' => 'text/xml'], '
            <SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
               <SOAP-ENV:Body>
                   <SOAP-ENV:Fault>
                       <faultcode>SOAP-ENV:MustUnderstand</faultcode>
                       <faultstring>SOAP Must Understand Error</faultstring>
                   </SOAP-ENV:Fault>
               </SOAP-ENV:Body>
            </SOAP-ENV:Envelope>')
        );
        $client = $this->factory->getClient(__DIR__ . '/../Fixtures/Soap/test.wsdl');

        try {
            $client->getSimple("foo");
            $this->assertTrue(false, "Exception is not thrown");
        } catch (FaultException $e) {
            $this->assertInstanceOf(Fault::class, $e->getFault());
        }
    }

}
