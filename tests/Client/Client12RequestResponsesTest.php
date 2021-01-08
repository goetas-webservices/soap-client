<?php

namespace GoetasWebservices\SoapServices\SoapClient\Tests\Client;

use GoetasWebservices\SoapServices\SoapClient\Arguments\Headers\Handler\HeaderHandler;
use GoetasWebservices\SoapServices\SoapClient\Arguments\Headers\Header;
use GoetasWebservices\SoapServices\SoapClient\Arguments\Headers\MustUnderstandHeader;
use GoetasWebservices\SoapServices\SoapClient\ClientFactory;
use GoetasWebservices\SoapServices\SoapClient\Envelope\Handler\FaultHandler;
use GoetasWebservices\SoapServices\SoapClient\Envelope\SoapEnvelope12\Parts\Fault;
use GoetasWebservices\SoapServices\SoapClient\Exception\FaultException;
use GoetasWebservices\SoapServices\SoapClient\Metadata\Generator\MetadataGenerator;
use GoetasWebservices\SoapServices\SoapClient\Metadata\Loader\DevMetadataLoader;
use GoetasWebservices\WsdlToPhp\Tests\Generator;
use GoetasWebservices\XML\SOAPReader\SoapReader;
use GoetasWebservices\XML\WSDLReader\DefinitionsReader;
use GoetasWebservices\Xsd\XsdToPhp\Naming\ShortNamingStrategy;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\HandlerRegistryInterface;
use JMS\Serializer\Serializer;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Client12RequestResponsesTest extends \PHPUnit_Framework_TestCase
{
    protected static $namespaces = [
        'http://www.example.org/test/' => "Ex"
    ];
    /**
     * @var Generator
     */
    protected static $generator;
    /**
     * @var \GuzzleHttp\Handler\MockHandler
     */
    protected $responseMock;

    protected $requestResponseStack = [];
    /**
     * @var ClientFactory
     */
    private $factory;
    /**
     * @var Serializer
     */
    private $serializer;

    public static function setUpBeforeClass()
    {
        self::$generator = new Generator(self::$namespaces);//, [], '/home/goetas/projects/soap-client/tmp');
        self::$generator->generate([__DIR__ . '/../Fixtures/test.wsdl']);
        self::$generator->registerAutoloader();
    }

    public static function tearDownAfterClass()
    {
        self::$generator->unRegisterAutoloader();
        //self::$generator->cleanDirectories();
    }

    public function setUp()
    {
        $generator = new Generator(self::$namespaces);
        $ref = new \ReflectionClass(Fault::class);
        $headerHandler = new HeaderHandler();
        $this->serializer = $generator->buildSerializer(function (HandlerRegistryInterface $h) use ($headerHandler) {
            $h->registerSubscribingHandler($headerHandler);
            $h->registerSubscribingHandler(new FaultHandler());
        }, [
            'GoetasWebservices\SoapServices\SoapClient\Envelope\SoapEnvelope12' => dirname($ref->getFileName()) . '/../../../Resources/metadata/jms12',
            'GoetasWebservices\SoapServices\SoapClient\Envelope\SoapEnvelope' => dirname($ref->getFileName()) . '/../../../Resources/metadata/jms',
        ]);

        $this->responseMock = new MockHandler();
        $history = Middleware::history($this->requestResponseStack);

        $handler = HandlerStack::create($this->responseMock);
        $handler->push($history);

        $guzzle = new Client(['handler' => $handler]);


        $naming = new ShortNamingStrategy();
        $dispatcher = new EventDispatcher();
        $wsdlReader = new DefinitionsReader(null, $dispatcher);
        $soapReader = new SoapReader();
        $dispatcher->addSubscriber($soapReader);

        $metadataGenerator = new MetadataGenerator($naming, self::$namespaces);
        $metadataLoader = new DevMetadataLoader($metadataGenerator, $soapReader, $wsdlReader);


        $this->factory = new ClientFactory($metadataLoader, $this->serializer);
        $this->factory->setHttpClient(new GuzzleAdapter($guzzle));
        $this->factory->setHeaderHandler($headerHandler);
    }

    public function testGetLastRequestMessage()
    {
        $httpResponse = new Response(200, ['Content-Type' => 'application/soap+xml'], '
        <SOAP:Envelope xmlns:SOAP="http://www.w3.org/2003/05/soap-envelope">
          <SOAP:Body>
            <ns-b3c6b39d:getSimpleResponse xmlns:ns-b3c6b39d="http://www.example.org/test/">
              <out><![CDATA[A]]></out>
            </ns-b3c6b39d:getSimpleResponse>
          </SOAP:Body>
        </SOAP:Envelope>');

        $this->responseMock->append($httpResponse);

        $client = $this->factory->getClient(__DIR__ . '/../Fixtures/test.wsdl', 'testSOAP12');
        $this->assertNull($client->__getLastRequestMessage());

        /**
         * @var $response \Ex\GetSimpleResponse
         */
        $response = $client->getSimple("foo");
        $this->assertInstanceOf('Psr\Http\Message\RequestInterface', $client->__getLastRequestMessage());
        $this->assertXmlStringEqualsXmlString(
            '<SOAP:Envelope xmlns:SOAP="http://www.w3.org/2003/05/soap-envelope">
              <SOAP:Body>
                <ns-b3c6b39d:getSimple xmlns:ns-b3c6b39d="http://www.example.org/test/">
                  <in><![CDATA[foo]]></in>
                </ns-b3c6b39d:getSimple>
              </SOAP:Body>
            </SOAP:Envelope>',
            (string)$client->__getLastRequestMessage()->getBody());
    }

    public function testGetLastResponseMessage()
    {
        $httpResponse = new Response(200, ['Content-Type' => 'application/soap+xml'], '
        <SOAP:Envelope xmlns:SOAP="http://www.w3.org/2003/05/soap-envelope">
          <SOAP:Body>
            <ns-b3c6b39d:getSimpleResponse xmlns:ns-b3c6b39d="http://www.example.org/test/">
              <out><![CDATA[A]]></out>
            </ns-b3c6b39d:getSimpleResponse>
          </SOAP:Body>
        </SOAP:Envelope>');

        $this->responseMock->append($httpResponse);

        $client = $this->factory->getClient(__DIR__ . '/../Fixtures/test.wsdl', 'testSOAP12');
        $this->assertNull($client->__getLastResponseMessage());

        /**
         * @var $response \Ex\GetSimpleResponse
         */
        $response = $client->getSimple("foo");
        $this->assertInstanceOf('Psr\Http\Message\ResponseInterface', $client->__getLastResponseMessage());
        $this->assertSame($httpResponse, $client->__getLastResponseMessage());
    }

    public function testGetSimple()
    {
        $httpResponse = new Response(200, ['Content-Type' => 'application/soap+xml'], '
        <SOAP:Envelope xmlns:SOAP="http://www.w3.org/2003/05/soap-envelope">
          <SOAP:Body>
            <ns-b3c6b39d:getSimpleResponse xmlns:ns-b3c6b39d="http://www.example.org/test/">
              <out><![CDATA[A]]></out>
            </ns-b3c6b39d:getSimpleResponse>
          </SOAP:Body>
        </SOAP:Envelope>');

        $this->responseMock->append($httpResponse);

        $client = $this->factory->getClient(__DIR__ . '/../Fixtures/test.wsdl', 'testSOAP12');
        /**
         * @var $response \Ex\GetSimpleResponse
         */
        $response = $client->getsimple("foo");
        $this->assertInstanceOf('Ex\GetSimpleResponse', $response);
        $this->assertEquals("A", $response->getOut());
    }

    public function testGetSimpleUnwrapped()
    {
        $httpResponse = new Response(200, ['Content-Type' => 'application/soap+xml'], '
        <SOAP:Envelope xmlns:SOAP="http://www.w3.org/2003/05/soap-envelope">
          <SOAP:Body>
            <ns-b3c6b39d:getSimpleResponse xmlns:ns-b3c6b39d="http://www.example.org/test/">
              <out><![CDATA[A]]></out>
            </ns-b3c6b39d:getSimpleResponse>
          </SOAP:Body>
        </SOAP:Envelope>');
        $this->responseMock->append($httpResponse);

        $naming = new ShortNamingStrategy();
        $dispatcher = new EventDispatcher();
        $wsdlReader = new DefinitionsReader(null, $dispatcher);
        $soapReader = new SoapReader();
        $dispatcher->addSubscriber($soapReader);

        $metadataGenerator = new MetadataGenerator($naming, self::$namespaces);
        $metadataGenerator->setUnwrap(true);
        $metadataReader = new DevMetadataLoader($metadataGenerator, $soapReader, $wsdlReader);

        $this->factory->setMetadataReader($metadataReader);
        $client = $this->factory->getClient(__DIR__ . '/../Fixtures/test.wsdl', 'testSOAP12');
        /**
         * @var $response \Ex\GetSimpleResponse
         */
        $response = $client->getSimple("foo");
        $this->assertSame("A", $response);
    }

    public function testHeaders()
    {
        $httpResponse = new Response(200, ['Content-Type' => 'application/soap+xml'], '
        <SOAP:Envelope xmlns:SOAP="http://www.w3.org/2003/05/soap-envelope">
          <SOAP:Body>
            <ns-b3c6b39d:getSimpleResponse xmlns:ns-b3c6b39d="http://www.example.org/test/">
              <out><![CDATA[A]]></out>
            </ns-b3c6b39d:getSimpleResponse>
          </SOAP:Body>
        </SOAP:Envelope>');

        $this->responseMock->append($httpResponse);
        $this->responseMock->append($httpResponse);

        $client = $this->factory->getClient(__DIR__ . '/../Fixtures/test.wsdl', 'testSOAP12', null, true);

        $mp = new \Ex\GetReturnMultiParam();
        $mp->setIn("foo");

        $client->getSimple("foo", new Header($mp));
        $client->getSimple("foo", new MustUnderstandHeader($mp));
        $this->assertXmlStringEqualsXmlString(
            '<SOAP:Envelope xmlns:SOAP="http://www.w3.org/2003/05/soap-envelope">
              <SOAP:Body>
                <ns-b3c6b39d:getSimple xmlns:ns-b3c6b39d="http://www.example.org/test/">
                  <in><![CDATA[foo]]></in>
                </ns-b3c6b39d:getSimple>
              </SOAP:Body>
              <SOAP:Header>
                <ns-b3c6b39d:getReturnMultiParam xmlns:ns-b3c6b39d="http://www.example.org/test/">
                  <in><![CDATA[foo]]></in>
                </ns-b3c6b39d:getReturnMultiParam>
              </SOAP:Header>
            </SOAP:Envelope>',
            (string)$this->requestResponseStack[0]['request']->getBody());

        $this->assertXmlStringEqualsXmlString(
            '<SOAP:Envelope xmlns:SOAP="http://www.w3.org/2003/05/soap-envelope">
              <SOAP:Body>
                <ns-b3c6b39d:getSimple xmlns:ns-b3c6b39d="http://www.example.org/test/">
                  <in><![CDATA[foo]]></in>
                </ns-b3c6b39d:getSimple>
              </SOAP:Body>
              <SOAP:Header>
                <ns-b3c6b39d:getReturnMultiParam xmlns:ns-b3c6b39d="http://www.example.org/test/" xmlns:SOAP="http://www.w3.org/2003/05/soap-envelope" SOAP:mustUnderstand="true">
                  <in><![CDATA[foo]]></in>
                </ns-b3c6b39d:getReturnMultiParam>
              </SOAP:Header>
            </SOAP:Envelope>',
            (string)$this->requestResponseStack[1]['request']->getBody());
    }

    public function testResponseHeaders()
    {
        $httpResponse = new Response(200, ['Content-Type' => 'application/soap+xml'],
            '<soapenv:Envelope 
                    xmlns:soapenv="http://www.w3.org/2003/05/soap-envelope" 
                    xmlns:test="http://www.example.org/test/">
               <soapenv:Header>
                  <test:authHeader>
                     <user>username</user>
                     <pwd>pass</pwd>
                  </test:authHeader>
               </soapenv:Header>
               <soapenv:Body>
                  <test:responseHeaderMessagesResponse>
                     <out>str</out>
                  </test:responseHeaderMessagesResponse>
               </soapenv:Body>
            </soapenv:Envelope>');

        $this->responseMock->append($httpResponse);


        $client = $this->factory->getClient(__DIR__ . '/../Fixtures/test.wsdl', 'testSOAP12');

        $res = $client->responseHeaderMessages("foo");
        $this->assertEquals("str", $res->getOut());
    }

    public function testNoOutput()
    {
        $this->responseMock->append(
            new Response(200, ['Content-Type' => 'application/soap+xml'], '
            <SOAP:Envelope xmlns:SOAP="http://www.w3.org/2003/05/soap-envelope">
              <SOAP:Body />
            </SOAP:Envelope>')
        );
        $client = $this->factory->getClient(__DIR__ . '/../Fixtures/test.wsdl', 'testSOAP12', null, true);

        $response = $client->noOutput("foo");
        $this->assertNull($response);
    }

    public function testNoInput()
    {
        $this->responseMock->append(
            new Response(200, ['Content-Type' => 'application/soap+xml'], '
            <SOAP:Envelope xmlns:SOAP="http://www.w3.org/2003/05/soap-envelope">
              <SOAP:Body>
                <ns-b3c6b39d:noInputResponse xmlns:ns-b3c6b39d="http://www.example.org/test/">
                  <out><![CDATA[A]]></out>
                </ns-b3c6b39d:noInputResponse>
              </SOAP:Body>
            </SOAP:Envelope>')
        );
        $client = $this->factory->getClient(__DIR__ . '/../Fixtures/test.wsdl', 'testSOAP12', null, true);

        $client->noInput("foo");
        $this->assertXmlStringEqualsXmlString(
            '<SOAP:Envelope xmlns:SOAP="http://www.w3.org/2003/05/soap-envelope"/>',
            (string)$this->requestResponseStack[0]['request']->getBody()
        );
    }

    public function testNoBoth()
    {
        $this->responseMock->append(
            new Response(
                200,
                ['Content-Type' => 'application/soap+xml'],
                '<SOAP:Envelope xmlns:SOAP="http://www.w3.org/2003/05/soap-envelope"/>'
            )
        );
        $client = $this->factory->getClient(__DIR__ . '/../Fixtures/test.wsdl', 'testSOAP12', null, true);

        $response = $client->noBoth("foo");
        $this->assertNull($response);
        $this->assertXmlStringEqualsXmlString(
            '<SOAP:Envelope xmlns:SOAP="http://www.w3.org/2003/05/soap-envelope"/>',
            (string)$this->requestResponseStack[0]['request']->getBody()
        );
    }

    public function testReturnMultiParam()
    {
        $this->responseMock->append(
            new Response(
                200,
                ['Content-Type' => 'application/soap+xml'],
                '<SOAP:Envelope xmlns:SOAP="http://www.w3.org/2003/05/soap-envelope">
              <SOAP:Body>
                <ns-b3c6b39d:getReturnMultiParamResponse xmlns:ns-b3c6b39d="http://www.example.org/test/">
                  <out><![CDATA[foo]]></out>
                </ns-b3c6b39d:getReturnMultiParamResponse>
                <other-param><![CDATA[str]]></other-param>
              </SOAP:Body>
            </SOAP:Envelope>'
            )
        );
        $client = $this->factory->getClient(__DIR__ . '/../Fixtures/test.wsdl', 'testSOAP12', null, true);

        $mp = new \Ex\GetReturnMultiParam();
        $mp->setIn("foo");
        $return = $client->getReturnMultiParam($mp);
        $this->assertCount(2, $return);
        $this->assertEquals($return['otherParam'], "str");
        $this->assertInstanceOf(\Ex\GetReturnMultiParamResponse::class, $return['parameters']);
        $this->assertEquals($return['parameters']->getOut(), "foo");

        $this->assertXmlStringEqualsXmlString(
            '<SOAP:Envelope xmlns:SOAP="http://www.w3.org/2003/05/soap-envelope">
              <SOAP:Body>
                <ns-b3c6b39d:getReturnMultiParam xmlns:ns-b3c6b39d="http://www.example.org/test/">
                  <in><![CDATA[foo]]></in>
                </ns-b3c6b39d:getReturnMultiParam>
              </SOAP:Body>
            </SOAP:Envelope>',
            (string)$this->requestResponseStack[0]['request']->getBody());
    }

    public function testMultiParamRequest()
    {
        $this->responseMock->append(
            new Response(
                200,
                ['Content-Type' => 'application/soap+xml'],
                '<SOAP:Envelope xmlns:SOAP="http://www.w3.org/2003/05/soap-envelope">
              <SOAP:Body>
                <ns-b3c6b39d:getMultiParamResponse xmlns:ns-b3c6b39d="http://www.example.org/test/">
                  <out><![CDATA[A]]></out>
                </ns-b3c6b39d:getMultiParamResponse>
              </SOAP:Body>
            </SOAP:Envelope>'
            )
        );
        $client = $this->factory->getClient(__DIR__ . '/../Fixtures/test.wsdl', 'testSOAP12', null, true);

        $mp = new \Ex\GetMultiParam();
        $mp->setIn("foo");
        $client->getMultiParam($mp, "str");

        $this->assertXmlStringEqualsXmlString(
            '<SOAP:Envelope xmlns:SOAP="http://www.w3.org/2003/05/soap-envelope">
              <SOAP:Body>
                <ns-b3c6b39d:getMultiParam xmlns:ns-b3c6b39d="http://www.example.org/test/">
                  <in><![CDATA[foo]]></in>
                </ns-b3c6b39d:getMultiParam>
                <other-param><![CDATA[str]]></other-param>
              </SOAP:Body>
            </SOAP:Envelope>',
            (string)$this->requestResponseStack[0]['request']->getBody());
    }

    public function getErrorResponses()
    {
        return [
            [new Response(500, ['Content-Type' => 'application/soap+xml'], '<foo/>')],
            [new Response(500, ['Content-Type' => 'text/html'])],
            [new Response(404, ['Content-Type' => 'application/soap+xml'], '<foo/>')],
            [new Response(404, ['Content-Type' => 'text/html'])],
            [new Response(200, ['Content-Type' => 'text/html'])],
            [new Response(200, ['Content-Type' => 'application/soap+xml'], '
            <SOAP-ENV:Envelope xmlns:SOAP-ENV="http://www.w3.org/2003/05/soap-envelope">
               <SOAP-ENV:Body>
                   <SOAP-ENV:Fault></SOAP-ENV:Fault>
               </SOAP-ENV:Body>
            </SOAP-ENV:Envelope>')]
            //[new Response(200, ['Content-Type' => 'application/soap+xml'], '<foo/>')],
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
        $client = $this->factory->getClient(__DIR__ . '/../Fixtures/test.wsdl', 'testSOAP12');
        $client->getSimple("foo");
    }

    public function testApplicationError()
    {
        $this->responseMock->append(
            new Response(500, ['Content-Type' => 'application/soap+xml'], '
            <SOAP-ENV:Envelope xmlns:SOAP-ENV="http://www.w3.org/2003/05/soap-envelope">
               <SOAP-ENV:Body>
                   <SOAP-ENV:Fault>
                       <SOAP-ENV:Code>
                            <SOAP-ENV:Value>server</SOAP-ENV:Value>
                       </SOAP-ENV:Code>
                   </SOAP-ENV:Fault>
               </SOAP-ENV:Body>
            </SOAP-ENV:Envelope>')
        );
        $client = $this->factory->getClient(__DIR__ . '/../Fixtures/test.wsdl', 'testSOAP12');

        try {
            $client->getSimple("foo");
            $this->assertTrue(false, "Exception is not thrown");
        } catch (FaultException $e) {
            $this->assertInstanceOf(Fault::class, $e->getFault());
        }
    }

    public function testSerializerContextParametersAreAdded()
    {
        // Override serializer with a custom handler for asserting the context parameters
        $generator = new Generator(self::$namespaces);
        $ref = new \ReflectionClass(Fault::class);
        $serializer = $this->serializer = $generator->buildSerializer(function (HandlerRegistryInterface $h) {
            $h->registerHandler(GraphNavigator::DIRECTION_SERIALIZATION, 'Ex\SoapEnvelope12\Messages\GetSimpleInput', 'xml',
                function($visitor, \Ex\SoapEnvelope12\Messages\GetSimpleInput $obj, array $type, Context $context) {

                    self::assertEquals('http://www.example.org/12', $context->getAttribute('soapEndpoint'));
                    self::assertEquals('http://www.example.org/test/getSimple', $context->getAttribute('soapAction'));

                    throw new SerializerHandlerAssertionsWereExecuted('Stop serialization, test has finished');
                });
        }, [
            'GoetasWebservices\SoapServices\SoapClient\Envelope\SoapEnvelope12' => dirname($ref->getFileName()) . '/../../../Resources/metadata/jms12',
            'GoetasWebservices\SoapServices\SoapClient\Envelope\SoapEnvelope' => dirname($ref->getFileName()) . '/../../../Resources/metadata/jms',
        ]);

        $this->factory->setSerializer($serializer);
        $client = $this->factory->getClient(__DIR__ . '/../Fixtures/test.wsdl', 'testSOAP12');

        // Assert that subscribing handler with assertions was executed
        self::expectException(SerializerHandlerAssertionsWereExecuted::class);

        $client->getSimple('foo');
    }
}

class SerializerHandlerAssertionsWereExecuted extends \Exception {}