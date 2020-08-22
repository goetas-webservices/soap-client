<?php


namespace GoetasWebservices\SoapServices\SoapClient\Tests\Client;

use GoetasWebservices\SoapServices\SoapClient\Arguments\Headers\Handler\HeaderHandler;
use GoetasWebservices\SoapServices\SoapClient\ClientFactory;
use GoetasWebservices\SoapServices\SoapClient\Envelope\Handler\FaultHandler;
use GoetasWebservices\SoapServices\SoapClient\Envelope\SoapEnvelope\Parts\Fault;
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
use JMS\Serializer\Handler\HandlerRegistryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class LocalTest extends TestCase
{
    protected static $namespaces = [
        'urn:example:local' => 'Example\Local'
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

    public static function setUpBeforeClass(): void
    {
        self::$generator = new LocalGenerator(self::$namespaces);
        self::$generator->generate([__DIR__ . '/../Fixtures/Local/local.wsdl']);
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
        $serializer = self::$generator->buildSerializer(function (HandlerRegistryInterface $h) use ($headerHandler) {
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


        $this->factory = new ClientFactory($metadataLoader, $serializer);
        $this->factory->setHttpClient(new GuzzleAdapter($guzzle));
        $this->factory->setHeaderHandler($headerHandler);
    }

    public function testLocalCheckOperation()
    {
        $httpResponse = new Response(200, ['Content-Type' => 'application/soap+xml'], '
 <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:urn="urn:example:local">
   <soap:Header/>
   <soap:Body>
      <urn:element2>
         <data>
            <Id>2</Id>
         </data>
      </urn:element2>
   </soap:Body>
</soap:Envelope>');

        $this->responseMock->append($httpResponse);

        $client = $this->factory->getClient(__DIR__ . '/../Fixtures/Local/local.wsdl');
        $this->assertNull($client->__getLastRequestMessage());

        $element1 = "Example\Local\Element1";
        $element2 = "Example\Local\Element2";
        $type2Type = "Example\Local\Type2Type";

        $input = new $element1();
        $e2 = new $element2();

        $data = new $type2Type();
        $data->setId(1);
        $e2->setData($data);
        $input->setElement2($e2);

        $response = $client->localCheckOperation($input);

        $this->assertXmlStringEqualsXmlString(
            '
<SOAP:Envelope xmlns:SOAP="http://www.w3.org/2003/05/soap-envelope">
  <SOAP:Body>
    <ns-9c385b9e:element1 xmlns:ns-9c385b9e="urn:example:local">
      <ns-9c385b9e:element2>
        <data>
          <Id>1</Id>
        </data>
      </ns-9c385b9e:element2>
    </ns-9c385b9e:element1>
  </SOAP:Body>
</SOAP:Envelope>',
            (string)$client->__getLastRequestMessage()->getBody());

        $this->assertNotNull($response);
        $this->assertNotNull($response->getData());
        $this->assertEquals(2, $response->getData()->getId());
    }

    public function testLocalCheckOperationReversed()
    {
        $httpResponse = new Response(200, ['Content-Type' => 'application/soap+xml'], '
<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:urn="urn:example:local">
   <soap:Header/>
   <soap:Body>
      <urn:element1>
         <urn:element2>
            <data>
               <Id>4</Id>
            </data>
         </urn:element2>
      </urn:element1>
   </soap:Body>
</soap:Envelope>');

        $this->responseMock->append($httpResponse);

        $client = $this->factory->getClient(__DIR__ . '/../Fixtures/Local/local.wsdl');
        $this->assertNull($client->__getLastRequestMessage());

        $element2 = "Example\Local\Element2";
        $type2Type = "Example\Local\Type2Type";

        $input = new $element2();
        $data = new $type2Type();
        $data->setId(3);
        $input->setData($data);

        $response = $client->localCheckOperationReversed($input);

        $this->assertXmlStringEqualsXmlString(
            '
<SOAP:Envelope xmlns:SOAP="http://www.w3.org/2003/05/soap-envelope">
  <SOAP:Body>
    <ns-9c385b9e:element2 xmlns:ns-9c385b9e="urn:example:local">
      <data>
        <Id>3</Id>
      </data>
    </ns-9c385b9e:element2>
  </SOAP:Body>
</SOAP:Envelope>
',
            (string)$client->__getLastRequestMessage()->getBody());

        $this->assertNotNull($response);
        $this->assertNotNull($response->getElement2());
        $this->assertNotNull($response->getElement2()->getData());
        $this->assertEquals(4, $response->getElement2()->getData()->getId());
    }
}