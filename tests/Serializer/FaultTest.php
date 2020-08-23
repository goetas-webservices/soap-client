<?php

namespace GoetasWebservices\SoapServices\SoapClient\Tests\Serializer;

use GoetasWebservices\SoapServices\SoapClient\Arguments\Headers\Handler\HeaderHandler;
use GoetasWebservices\SoapServices\SoapClient\Envelope\Handler\FaultHandler;
use GoetasWebservices\SoapServices\SoapClient\Envelope\SoapEnvelope\Messages\Fault as Fault11;
use GoetasWebservices\SoapServices\SoapClient\Envelope\SoapEnvelope12\Messages\Fault as Fault12;
use GoetasWebservices\WsdlToPhp\Tests\Generator;
use JMS\Serializer\Handler\HandlerRegistryInterface;
use JMS\Serializer\Serializer;
use PHPUnit\Framework\TestCase;

class FaultTest extends TestCase
{
    /**
     * @var Serializer
     */
    protected $serializer;

    public function setUp(): void
    {

        $generator = new Generator([]);
        $ref = new \ReflectionClass(Fault12::class);

        $headerHandler = new HeaderHandler();
        $this->serializer = $generator->buildSerializer(function (HandlerRegistryInterface $h) use ($headerHandler) {
            $h->registerSubscribingHandler($headerHandler);
            $h->registerSubscribingHandler(new FaultHandler());
        }, [
            'GoetasWebservices\SoapServices\SoapClient\Envelope\SoapEnvelope12' => dirname($ref->getFileName()) . '/../../../Resources/metadata/jms12',
            'GoetasWebservices\SoapServices\SoapClient\Envelope\SoapEnvelope' => dirname($ref->getFileName()) . '/../../../Resources/metadata/jms',
        ]);

    }

    public function testFault12()
    {
        $xml = '<?xml version="1.0" ?>
        <env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope">
            <env:Body>
                <env:Fault>
                    <env:Code>
                        <env:Value>env:Sender</env:Value>
                        <env:Subcode>
                            <env:Value>me</env:Value>
                        </env:Subcode>
                    </env:Code>
                    <env:Reason>
                        <env:Text xml:lang="en-US">Message does not have necessary info</env:Text>
                    </env:Reason>
                    <env:Role>http://gizmos.com/order</env:Role>
                    <env:Node>http://gizmos.com/node</env:Node>
                    <env:Detail>
                        <PO:order xmlns:PO="http://gizmos.com/orders/">A</PO:order>
                        <PO:confirmation xmlns:PO="http://gizmos.com/confirm">B</PO:confirmation>
                    </env:Detail>
                </env:Fault>
              </env:Body>
            </env:Envelope>                
        ';
        /**
         * @var $faultMessage Fault12
         */
        $faultMessage = $this->serializer->deserialize($xml, Fault12::class, 'xml');

        $fault = $faultMessage->getBody()->getFault();

        $this->assertEquals("env:Sender", $fault->getCode()->getValue());
        $this->assertEquals("env:Sender:me", strval($fault->getCode()));
        $this->assertEquals("http://gizmos.com/node", $fault->getNode());
        $this->assertEquals("http://gizmos.com/order", $fault->getRole());
        $this->assertEquals(["Message does not have necessary info"], $fault->getReason());
        $this->assertXmlStringEqualsXmlString('
                <a>
                    <PO:order xmlns:PO="http://gizmos.com/orders/">A</PO:order>
                    <PO:confirmation xmlns:PO="http://gizmos.com/confirm">B</PO:confirmation>
                </a>
                ', "<a>" . $fault->getDetail() . "</a>"
        );
    }

    public function testFault11()
    {
        $xml = '<?xml version="1.0" ?>
        <env:Envelope xmlns:env="http://schemas.xmlsoap.org/soap/envelope/">
            <env:Body>
             <env:Fault>
               <faultcode>env:Server</faultcode>
               <faultstring>Processing error</faultstring>
               <detail>
                   <PO:order xmlns:PO="http://gizmos.com/orders/">A</PO:order>
                   <PO:confirmation xmlns:PO="http://gizmos.com/confirm">B</PO:confirmation>
               </detail>
           </env:Fault>
          </env:Body>
        </env:Envelope>
       
    ';
        /**
         * @var $faultMessage Fault11
         */
        $faultMessage = $this->serializer->deserialize($xml, Fault11::class, 'xml');

        $fault = $faultMessage->getBody()->getFault();

        $this->assertEquals("env:Server", $fault->getCode());
        $this->assertEquals("Processing error", $fault->getString());
        $this->assertXmlStringEqualsXmlString('
                <a>
                    <PO:order xmlns:PO="http://gizmos.com/orders/">A</PO:order>
                    <PO:confirmation xmlns:PO="http://gizmos.com/confirm">B</PO:confirmation>
                </a>
                ', "<a>" . $fault->getDetail() . "</a>");
    }

}
