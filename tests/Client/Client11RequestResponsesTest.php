<?php

declare(strict_types=1);

namespace GoetasWebservices\SoapServices\SoapClient\Tests\Client;

use Ex\GetMultiParam;
use Ex\GetReturnMultiParam;
use Ex\GetReturnMultiParamResponse;
use GoetasWebservices\SoapServices\Metadata\Arguments\Headers\Header;
use GoetasWebservices\SoapServices\Metadata\Envelope\Fault as FaultBase;
use GoetasWebservices\SoapServices\Metadata\Envelope\SoapEnvelope\Messages\Fault;
use GoetasWebservices\SoapServices\Metadata\Generator\MetadataGenerator;
use GoetasWebservices\SoapServices\Metadata\Loader\DevMetadataLoader;
use GoetasWebservices\SoapServices\SoapClient\Client;
use GoetasWebservices\SoapServices\SoapClient\Exception\Fault11Exception;
use GoetasWebservices\XML\SOAPReader\SoapReader;
use GoetasWebservices\XML\WSDLReader\DefinitionsReader;
use GoetasWebservices\Xsd\XsdToPhp\Naming\ShortNamingStrategy;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Client11RequestResponsesTest extends RequestResponsesTest
{
    protected function getClient(): Client
    {
        return $this->factory->getClient(__DIR__ . '/../Fixtures/test.wsdl');
    }

    public function testGetLastRequestMessage(): void
    {
        $httpResponse = new Response(200, ['Content-Type' => 'text/xml'], '
        <SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/">
          <SOAP:Body>
            <ns-b3c6b39d:getSimpleResponse xmlns:ns-b3c6b39d="http://www.example.org/test/">
              <out><![CDATA[A]]></out>
            </ns-b3c6b39d:getSimpleResponse>
          </SOAP:Body>
        </SOAP:Envelope>');

        $this->responseMock->append($httpResponse);

        $this->client->setDebug(true);
        $this->assertNull($this->client->__getLastRequestMessage());

        /**
         * @var $response \Ex\GetSimpleResponse
         */
        $response = $this->client->getSimple('foo');
        $this->assertInstanceOf('Psr\Http\Message\RequestInterface', $this->client->__getLastRequestMessage());
        $this->assertXmlStringEqualsXmlString(
            '<SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/">
              <SOAP:Body>
                <ns-b3c6b39d:getSimple xmlns:ns-b3c6b39d="http://www.example.org/test/">
                  <in><![CDATA[foo]]></in>
                </ns-b3c6b39d:getSimple>
              </SOAP:Body>
            </SOAP:Envelope>',
            (string) $this->client->__getLastRequestMessage()->getBody()
        );
    }

    public function testGetLastResponseMessage(): void
    {
        $httpResponse = new Response(200, ['Content-Type' => 'text/xml'], '
        <SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/">
          <SOAP:Body>
            <ns-b3c6b39d:getSimpleResponse xmlns:ns-b3c6b39d="http://www.example.org/test/">
              <out><![CDATA[A]]></out>
            </ns-b3c6b39d:getSimpleResponse>
          </SOAP:Body>
        </SOAP:Envelope>');

        $this->responseMock->append($httpResponse);

        $this->client->setDebug(true);
        $this->assertNull($this->client->__getLastResponseMessage());

        /**
         * @var $response \Ex\GetSimpleResponse
         */
        $response = $this->client->getSimple('foo');
        $this->assertInstanceOf('Psr\Http\Message\ResponseInterface', $this->client->__getLastResponseMessage());
        $this->assertSame($httpResponse, $this->client->__getLastResponseMessage());
    }

    public function testGetSimple(): void
    {
        $httpResponse = new Response(200, ['Content-Type' => 'text/xml'], '
        <SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/">
          <SOAP:Body>
            <ns-b3c6b39d:getSimpleResponse xmlns:ns-b3c6b39d="http://www.example.org/test/">
              <out><![CDATA[A]]></out>
            </ns-b3c6b39d:getSimpleResponse>
          </SOAP:Body>
        </SOAP:Envelope>');

        $this->responseMock->append($httpResponse);

        /**
         * @var $response \Ex\GetSimpleResponse
         */
        $response = $this->client->getSimple('foo');
        $this->assertInstanceOf('Ex\GetSimpleResponse', $response);
        $this->assertEquals('A', $response->getOut());
    }

    public function testGetSimpleUnwrapped(): void
    {
        $httpResponse = new Response(200, ['Content-Type' => 'text/xml'], '
        <SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/">
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

        /**
         * @var $response \Ex\GetSimpleResponse
         */
        $response = $this->getClient()->getSimple('foo');
        $this->assertSame('A', $response);
    }

    public function testHeaders(): void
    {
        $httpResponse = new Response(200, ['Content-Type' => 'text/xml'], '
        <SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/">
          <SOAP:Body>
            <ns-b3c6b39d:getSimpleResponse xmlns:ns-b3c6b39d="http://www.example.org/test/">
              <out><![CDATA[A]]></out>
            </ns-b3c6b39d:getSimpleResponse>
          </SOAP:Body>
        </SOAP:Envelope>');

        $this->responseMock->append($httpResponse);
        $this->responseMock->append($httpResponse);

        $mp = new GetReturnMultiParam();
        $mp->setIn('foo');

        $this->client->getSimple('foo', new Header($mp));
        $this->client->getSimple('foo', (new Header($mp))->mustUnderstand());

        $this->assertXmlStringEqualsXmlString(
            '<SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/">
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
            (string) $this->requestResponseStack[0]['request']->getBody()
        );

        $this->assertXmlStringEqualsXmlString(
            '<SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/">
              <SOAP:Body>
                <ns-b3c6b39d:getSimple xmlns:ns-b3c6b39d="http://www.example.org/test/">
                  <in><![CDATA[foo]]></in>
                </ns-b3c6b39d:getSimple>
              </SOAP:Body>
              <SOAP:Header>
                <ns-b3c6b39d:getReturnMultiParam xmlns:ns-b3c6b39d="http://www.example.org/test/" xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/" SOAP:mustUnderstand="true">
                  <in><![CDATA[foo]]></in>
                </ns-b3c6b39d:getReturnMultiParam>
              </SOAP:Header>
            </SOAP:Envelope>',
            (string) $this->requestResponseStack[1]['request']->getBody()
        );
    }

    public function testResponseHeaders(): void
    {
        $httpResponse = new Response(
            200,
            ['Content-Type' => 'text/xml'],
            '<soapenv:Envelope 
                    xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" 
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
            </soapenv:Envelope>'
        );

        $this->responseMock->append($httpResponse);

        $res = $this->client->responseHeaderMessages('foo');
        $this->assertEquals('str', $res->getOut());
    }

    public function testNoOutput(): void
    {
        $this->responseMock->append(
            new Response(200, ['Content-Type' => 'text/xml'], '
            <SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/">
              <SOAP:Body />
            </SOAP:Envelope>')
        );

        $response = $this->client->noOutput('foo');
        $this->assertNull($response);
    }

    public function testNoInput(): void
    {
        $this->responseMock->append(
            new Response(200, ['Content-Type' => 'text/xml'], '
            <SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/">
              <SOAP:Body>
                <ns-b3c6b39d:noInputResponse xmlns:ns-b3c6b39d="http://www.example.org/test/">
                  <out><![CDATA[A]]></out>
                </ns-b3c6b39d:noInputResponse>
              </SOAP:Body>
            </SOAP:Envelope>')
        );

        $this->client->noInput('foo');
        $this->assertXmlStringEqualsXmlString(
            '<SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/"/>',
            (string) $this->requestResponseStack[0]['request']->getBody()
        );
    }

    public function testNoBoth(): void
    {
        $this->responseMock->append(
            new Response(
                200,
                ['Content-Type' => 'text/xml'],
                '<SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/"/>'
            )
        );

        $response = $this->client->noBoth('foo');
        $this->assertNull($response);
        $this->assertXmlStringEqualsXmlString(
            '<SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/"/>',
            (string) $this->requestResponseStack[0]['request']->getBody()
        );
    }

    public function testReturnMultiParam(): void
    {
        $this->responseMock->append(
            new Response(
                200,
                ['Content-Type' => 'text/xml'],
                '<SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/">
              <SOAP:Body>
                <ns-b3c6b39d:getReturnMultiParamResponse xmlns:ns-b3c6b39d="http://www.example.org/test/">
                  <out><![CDATA[foo]]></out>
                </ns-b3c6b39d:getReturnMultiParamResponse>
                <other-param><![CDATA[str]]></other-param>
              </SOAP:Body>
            </SOAP:Envelope>'
            )
        );

        $mp = new GetReturnMultiParam();
        $mp->setIn('foo');
        $return = $this->client->getReturnMultiParam($mp);

        $this->assertCount(2, $return);
        $this->assertEquals($return['otherParam'], 'str');

        $this->assertInstanceOf(GetReturnMultiParamResponse::class, $return['getReturnMultiParamResponse']);
        $this->assertEquals($return['getReturnMultiParamResponse']->getOut(), 'foo');

        $this->assertXmlStringEqualsXmlString(
            '<SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/">
              <SOAP:Body>
                <ns-b3c6b39d:getReturnMultiParam xmlns:ns-b3c6b39d="http://www.example.org/test/">
                  <in><![CDATA[foo]]></in>
                </ns-b3c6b39d:getReturnMultiParam>
              </SOAP:Body>
            </SOAP:Envelope>',
            (string) $this->requestResponseStack[0]['request']->getBody()
        );
    }

    public function testMultiParamRequest(): void
    {
        $this->responseMock->append(
            new Response(
                200,
                ['Content-Type' => 'text/xml'],
                '<SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/">
              <SOAP:Body>
                <ns-b3c6b39d:getMultiParamResponse xmlns:ns-b3c6b39d="http://www.example.org/test/">
                  <out><![CDATA[A]]></out>
                </ns-b3c6b39d:getMultiParamResponse>
              </SOAP:Body>
            </SOAP:Envelope>'
            )
        );

        $mp = new GetMultiParam();
        $mp->setIn('foo');
        $this->client->getMultiParam($mp, 'str');

        $this->assertXmlStringEqualsXmlString(
            '<SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/">
              <SOAP:Body>
                <ns-b3c6b39d:getMultiParam xmlns:ns-b3c6b39d="http://www.example.org/test/">
                  <in><![CDATA[foo]]></in>
                </ns-b3c6b39d:getMultiParam>
                <other-param><![CDATA[str]]></other-param>
              </SOAP:Body>
            </SOAP:Envelope>',
            (string) $this->requestResponseStack[0]['request']->getBody()
        );
    }

    /**
     * @dataProvider getHttpFaultCodes
     */
    public function testApplicationError(int $code): void
    {
        $this->responseMock->append(
            new Response($code, ['Content-Type' => 'text/xml'], '
            <SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
               <SOAP-ENV:Body>
                   <SOAP-ENV:Fault>
                       <faultcode>SOAP-ENV:MustUnderstand</faultcode>
                       <faultstring>SOAP Must Understand Error</faultstring>
                       <detail>
                           <faultInfo>
                              <faultType>Business Exception</faultType>
                              <faultCode>100</faultCode>
                              <message>some message</message>
                              <faultState>fault state</faultState>
                           </faultInfo>
                       </detail>
                   </SOAP-ENV:Fault>
               </SOAP-ENV:Body>
            </SOAP-ENV:Envelope>')
        );

        try {
            $this->client->getSimple('foo');
            $this->assertTrue(false, 'Exception is not thrown');
        } catch (Fault11Exception $e) {
            $this->assertInstanceOf(Fault::class, $e->getFault());
            $this->assertInstanceOf(FaultBase::class, $e->getFault()->getBody()->getFault());

            $detail = $e->getFault()->getBody()->getFault()->getRawDetail();
            $this->assertInstanceOf(\SimpleXMLElement::class, $detail);
            $this->assertXmlStringEqualsXmlString(trim('
              <faultInfo>
                <faultType>Business Exception</faultType>
                <faultCode>100</faultCode>
                <message>some message</message>
                <faultState>fault state</faultState>
              </faultInfo>
            '), $detail->asXML());
        }
    }
}
