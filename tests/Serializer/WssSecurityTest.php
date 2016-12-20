<?php

namespace GoetasWebservices\SoapServices\SoapClient\Tests\Serializer;

use Ex\SoapEnvelope12\Messages\RequestHeaderInput;
use Ex\SoapParts\RequestHeadersInput;
use GoetasWebservices\SoapServices\SoapClient\Arguments\Headers\Handler\HeaderHandler;
use GoetasWebservices\SoapServices\SoapClient\Arguments\Headers\Handler\HeaderPlaceholder;
use GoetasWebservices\SoapServices\SoapClient\Arguments\Headers\MustUnderstandHeader;
use GoetasWebservices\SoapServices\SoapClient\Envelope\Handler\FaultHandler;
use GoetasWebservices\SoapServices\SoapClient\Envelope\SoapEnvelope12\Messages\Fault as Fault12;
use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Security;
use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\SecurityKeyPair;
use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\WsSecurityFilterRequest;
use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\WssSecurityHeaderEventHandler;
use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\WssSecurityHeaderHandler;
use GoetasWebservices\WsdlToPhp\Tests\Generator;
use JMS\Serializer\EventDispatcher\EventDispatcher;
use JMS\Serializer\Handler\HandlerRegistryInterface;
use JMS\Serializer\Serializer;

class WssSecurityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Serializer
     */
    protected $serializer;


    /**
     * @var HeaderHandler
     */
    protected $headerHandler;
    protected static $generator;
    protected static $namespaces = [
        'http://www.example.org/test/' => "Ex",
        'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd' => 'GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Secext',
        'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd' => 'GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Utility',
        'http://www.w3.org/2000/09/xmldsig#' => 'GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign',
    ];


    public static function setUpBeforeClass()
    {
        self::$generator = new Generator(self::$namespaces);
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

        $generator = self::$generator;
        $ref = new \ReflectionClass(Fault12::class);

        $this->headerHandler = new HeaderHandler();
        $this->serializer = $generator->buildSerializer(function (HandlerRegistryInterface $h) {
            $h->registerSubscribingHandler($this->headerHandler);
            $hr = new WsSecurityFilterRequest();

            $keypair = new SecurityKeyPair();
            $filename = __DIR__.'/../Fixtures/clientkey.pem';
            $keypair->setPrivateKey(\ass\XmlSecurity\Key::RSA_SHA1, $filename);
            $filename = __DIR__.'/../Fixtures/clientcert.pem';
            $keypair->setPublicKey(\ass\XmlSecurity\Key::RSA_SHA1, $filename);

            $hr->setUserSecurityKeyObject($keypair);


            $keypair = new SecurityKeyPair();
            $keypair->setPrivateKey(\ass\XmlSecurity\Key::TRIPLEDES_CBC);
            $keypair->setPublicKey(\ass\XmlSecurity\Key::RSA_1_5, __DIR__.'/../Fixtures/servercert.pem');

            $hr->setServiceSecurityKeyObject($keypair);


            $sechandler = new WssSecurityHeaderHandler($hr);
            $h->registerSubscribingHandler($sechandler);
        }, [
            'GoetasWebservices\SoapServices\SoapClient\Envelope\SoapEnvelope12' => dirname($ref->getFileName()) . '/../../../Resources/metadata/jms12',
        ]);

    }

    public function testSerializeSecurity()
    {
        $headerPlaceholder = new HeaderPlaceholder();


        $security = new Security();
        $security->setUsername('foo');
        $security->setPassword('pass');

        $auth = new \Ex\AuthHeader();
        $auth->setUser('bar');

        $this->headerHandler->addHeaderData($headerPlaceholder, new MustUnderstandHeader($security));
        $this->headerHandler->addHeaderData($headerPlaceholder, new MustUnderstandHeader($auth));

        $env = new RequestHeaderInput();
        $env->setHeader($headerPlaceholder);

        $body = new \Ex\SoapParts\RequestHeaderInput();
        $p = new \Ex\RequestHeader();
        $p->setIn("sss");
        $body->setParameters($p);
        $env->setBody($body);


        $xml = $this->serializer->serialize($env, 'xml');


        echo $xml;
        return;
        $this->assertXmlStringEqualsXmlString('<?xml version="1.0" encoding="UTF-8"?>
        <Security xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
          <ns-e2891a80:Timestamp xmlns:ns-e2891a80="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
            <ns-e2891a80:Created><![CDATA[2010-01-01T00:00:00.000Z]]></ns-e2891a80:Created>
            <ns-e2891a80:Expires><![CDATA[2010-01-01T00:05:00.000Z]]></ns-e2891a80:Expires>
          </ns-e2891a80:Timestamp>
          <UsernameToken xmlns:ns-e2891a80="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
            <Username><![CDATA[foo]]></Username>
            <Nonce><![CDATA[eHg=]]></Nonce>
            <ns-e2891a80:Created xmlns:ns-e2891a80="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"><![CDATA[2010-01-01T00:00:00.000Z]]></ns-e2891a80:Created>
            <Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordDigest"><![CDATA[aw4f+UvW8DfCB+ycsauhJzRXFHo=]]></Password>
          </UsernameToken>
        </Security>', $xml);
    }


}
