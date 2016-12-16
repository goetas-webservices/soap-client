<?php

namespace GoetasWebservices\SoapServices\SoapClient\Tests\Serializer;

use GoetasWebservices\SoapServices\SoapClient\Arguments\Headers\Handler\HeaderHandler;
use GoetasWebservices\SoapServices\SoapClient\Envelope\Handler\FaultHandler;
use GoetasWebservices\SoapServices\SoapClient\Envelope\SoapEnvelope\Messages\Fault as Fault11;
use GoetasWebservices\SoapServices\SoapClient\Envelope\SoapEnvelope12\Messages\Fault as Fault12;
use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Security;
use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\WssSecurityHeaderEventHandler;
use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\WssSecurityHeaderHandler;
use GoetasWebservices\WsdlToPhp\Tests\Generator;
use JMS\Serializer\EventDispatcher\EventDispatcher;
use JMS\Serializer\Handler\HandlerRegistryInterface;
use JMS\Serializer\Serializer;
use Zend\Stdlib\DateTime;

class WssSecurityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Serializer
     */
    protected $serializer;

    public function setUp()
    {

        $generator = new Generator([]);
        $ref = new \ReflectionClass(Fault12::class);

        $headerHandler = new HeaderHandler();
        $this->serializer = $generator->buildSerializer(function (HandlerRegistryInterface $h) use ($headerHandler) {
            $h->registerSubscribingHandler($headerHandler);
            $h->registerSubscribingHandler(new FaultHandler());
            $sechandler = new WssSecurityHeaderHandler();
            $sechandler->setNonce('xx');
            $h->registerSubscribingHandler($sechandler);
        }, [
            'GoetasWebservices\SoapServices\SoapClient\Envelope\SoapEnvelope12' => dirname($ref->getFileName()) . '/../../../Resources/metadata/jms12',
            'GoetasWebservices\SoapServices\SoapClient\WssWsSecurity' => dirname($ref->getFileName()) . '/../../../Resources/metadata/wss-ws-security',
        ], function(EventDispatcher $ev) {
            $ev->addSubscriber(new WssSecurityHeaderEventHandler());
        });

    }

    public function testFault12()
    {
        $wssec = new Security();
        $wssec->setUsername('foo');
        $wssec->setPassword('bar');
        $wssec->setTimestamp(new \DateTime('2010-01-01 00:00:00', new \DateTimeZone('UTC')));

        $xml = $this->serializer->serialize($wssec, 'xml');

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
