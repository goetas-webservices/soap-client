<?php

namespace GoetasWebservices\SoapServices\SoapClient\Tests\Serializer;

use Ex\SoapEnvelope12\Messages\RequestHeaderInput;
use Ex\SoapParts\RequestHeadersInput;
use GoetasWebservices\SoapServices\SoapClient\Arguments\Headers\Handler\HeaderHandler;
use GoetasWebservices\SoapServices\SoapClient\Arguments\Headers\Handler\HeaderPlaceholder;
use GoetasWebservices\SoapServices\SoapClient\Arguments\Headers\MustUnderstandHeader;
use GoetasWebservices\SoapServices\SoapClient\Envelope\SoapEnvelope12\Messages\Fault as Fault12;
use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Security;
use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\SecurityKeyPair;
use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Serializer\WsSecurityFilterRequest;
use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Serializer\WsSecurityFilterResponse;
use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Serializer\WssSecurityHeaderEventHandler;
use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Serializer\WssSecurityHeaderEventListener;
use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Serializer\WssSecurityHeaderHandler;
use GoetasWebservices\WsdlToPhp\Tests\Generator;
use JMS\Serializer\EventDispatcher\EventDispatcherInterface;
use JMS\Serializer\Handler\HandlerRegistryInterface;
use JMS\Serializer\Serializer;

class WssSecurityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var WsSecurityFilterRequest
     */
    protected $requestFilter;


    /**
     * @var WsSecurityFilterResponse
     */
    protected $responseFilter;


    /**
     * @var HeaderHandler
     */
    protected $headerHandler;
    /**
     * @var Generator
     */
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

        $this->requestFilter = new WsSecurityFilterRequest();
        $this->responseFilter = new WsSecurityFilterResponse();

        $keypair1 = new SecurityKeyPair();
        $keypair1->setPrivateKey(\ass\XmlSecurity\Key::RSA_SHA1, __DIR__.'/../Fixtures/client_private_key.pem');
        $keypair1->setPublicKey(\ass\XmlSecurity\Key::RSA_SHA1, __DIR__.'/../Fixtures/client_public_key.pem');

        $this->requestFilter->setUserSecurityKeyObject($keypair1);

        $keypair2 = new SecurityKeyPair();
        $keypair2->setPrivateKey(\ass\XmlSecurity\Key::TRIPLEDES_CBC);
        $keypair2->setPublicKey(\ass\XmlSecurity\Key::RSA_1_5, __DIR__.'/../Fixtures/server_public_key.pem');

        $this->requestFilter->setServiceSecurityKeyObject($keypair2);

        $keypair3 = new SecurityKeyPair();
        $keypair3->setPrivateKey(\ass\XmlSecurity\Key::RSA_SHA1, __DIR__.'/../Fixtures/server_private_key.pem');
        $keypair3->setPublicKey(\ass\XmlSecurity\Key::RSA_SHA1, __DIR__.'/../Fixtures/client_public_key.pem');


        $this->responseFilter->setUserSecurityKeyObject($keypair3);

        $this->headerHandler = new HeaderHandler();
        $this->serializer = $generator->buildSerializer(function (HandlerRegistryInterface $h) {
            $h->registerSubscribingHandler($this->headerHandler);

            $sechandler = new WssSecurityHeaderHandler($this->requestFilter);
            $h->registerSubscribingHandler($sechandler);
        }, [
            'GoetasWebservices\SoapServices\SoapClient\Envelope\SoapEnvelope12' => dirname($ref->getFileName()) . '/../../../Resources/metadata/jms12',
        ], function(EventDispatcherInterface $d) {
            $d->addSubscriber(new WssSecurityHeaderEventListener($this->responseFilter));
        });

    }

    public function testSerializeSecurity()
    {
        $headerPlaceholder = new HeaderPlaceholder();

        $this->requestFilter->setSecurityOptionsEncryption(WsSecurityFilterRequest::TOKEN_REFERENCE_SUBJECT_KEY_IDENTIFIER);

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

        $expectedString = '<?xml version="1.0" encoding="UTF-8"?>
<SOAP:Envelope xmlns:SOAP="http://www.w3.org/2003/05/soap-envelope" xmlns:ws="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:ns-b3c6b39d="http://www.example.org/test/" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xenc="http://www.w3.org/2001/04/xmlenc#">
  <SOAP:Body wsu:Id="Id-499fd1c0-ea81-447b-aef3-3307305ce8b1">
    <xenc:EncryptedData Id="Id-0f1d5cf0-d881-4655-8310-6f69bbdbedc3" Type="http://www.w3.org/2001/04/xmlenc#Content">
      <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#tripledes-cbc"/>
      <ds:KeyInfo xmlns="http://www.w3.org/2000/09/xmldsig#" xmlns:default="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
        <ws:SecurityTokenReference xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
          <ws:Reference URI="#EncKey-1f3e9b24-b44a-4c80-8d3c-8f2e685f3c2e"/>
        </ws:SecurityTokenReference>
      </ds:KeyInfo>
      <xenc:CipherData>
        <xenc:CipherValue>aPfDzwzxuVaqRXvKfnBIKxqyPgdyZvBLm0JyNXRyZDn7HNHoX7cuAtT5tHne9lQP4/9DRXyhA/1HdxY755dv8IS9PMcNpGGEdhwy2UgTko0zDynpwUcvrslxukFehskvhhyFAxIShmbFJzRZjbq/OYeggiXWhcg+/tezDuyNbHSdnWcSq8w9+oO5UTog4Uj47DWY+2r6iP1/Ln7boMi8wn2xABIxkBFFU32z51tngtMIzvWyoFFDf9xc3jDgz5Vd9t4jUbm9akH68m2TAngGahGOEzIa3JudVLEkDLJXT8UCZkuHmP85r2QGEMz/8cMJOE8cjDKtAlbMy1lL7kbzSFA2yYZBEkyXM+F1y1E/A7YS+/mrQ2VqdDldEQNtfSiaV/VPEonrGJkFrSWlYeEUWF2Cg8rEerDrmYZX3vLyVxrtjNHSA5JVaQOpwWO0BGajIuEiCgoHRbA1zPmtpoZh9u39Hd/F8bg9ZBDCOky1L83f0IW5LHUjYOv0FxqFXftdKvnF7/aDbiZnhX3330p/Iw2guOWpwJTHyOilfMXo0sC3jY/hB36jIznYb+TtAMKB3lki47jVj2DREYQDh/nsH1HhbpgFe+JhtFvV3ewZGjc=</xenc:CipherValue>
      </xenc:CipherData>
    </xenc:EncryptedData>
  </SOAP:Body>
  <SOAP:Header>
    <ws:Security SOAP:mustUnderstand="true">
      <wsu:Timestamp wsu:Id="Id-c640e75b-2c27-4a96-80d9-adb0914d6e5f">
        <wsu:Created>2016-12-21T14:04:53.000Z</wsu:Created>
        <wsu:Expires>2016-12-21T14:09:53.000Z</wsu:Expires>
      </wsu:Timestamp>
      <ws:UsernameToken wsu:Id="Id-6abc3fa3-d2b7-4026-befd-34c05d440f2d">
        <ws:Username>foo</ws:Username>
      </ws:UsernameToken>
      <ws:BinarySecurityToken EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary" ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3" wsu:Id="CertId-e5ce21c4-e68e-47c3-9695-4f5a09e7efe1"/>
      <xenc:EncryptedKey Id="EncKey-1f3e9b24-b44a-4c80-8d3c-8f2e685f3c2e">
        <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#rsa-1_5"/>
        <ds:KeyInfo>
          <ws:SecurityTokenReference xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <ws:KeyIdentifier EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary" ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#509SubjectKeyIdentifier"></ws:KeyIdentifier>
          </ws:SecurityTokenReference>
        </ds:KeyInfo>
        <xenc:CipherData>
          <xenc:CipherValue>og8QzG1KkeF3W302te9V5leP4Mnvrb+qJEld055MFCYUFN6znwR0dbp/P3YRqV0efBIkF6MA5Gj4TX6TkyIvHjELTJNyQDpfrLWXZBhzP+6AdrCQzpWZ0p3sYUJXDVoYXEKhfPYmdpReMoK/H4cmcfdZj2B/ZcMmL/ZCQNxOAv4=</xenc:CipherValue>
        </xenc:CipherData>
        <xenc:ReferenceList>
          <xenc:DataReference URI="#Id-0f1d5cf0-d881-4655-8310-6f69bbdbedc3"/>
        </xenc:ReferenceList>
      </xenc:EncryptedKey>
      <ds:Signature>
        <ds:SignedInfo>
          <ds:CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
          <ds:SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"/>
          <ds:Reference URI="#Id-499fd1c0-ea81-447b-aef3-3307305ce8b1">
            <ds:Transforms>
              <ds:Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
            </ds:Transforms>
            <ds:DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>
            <ds:DigestValue>ji4+Hoi++5jtopiElK3EFL/AcTM=</ds:DigestValue>
          </ds:Reference>
          <ds:Reference URI="#Id-c640e75b-2c27-4a96-80d9-adb0914d6e5f">
            <ds:Transforms>
              <ds:Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
            </ds:Transforms>
            <ds:DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>
            <ds:DigestValue>H4BPAIlGlXwRxZcXxgJTgBMxQ5Y=</ds:DigestValue>
          </ds:Reference>
          <ds:Reference URI="#Id-6abc3fa3-d2b7-4026-befd-34c05d440f2d">
            <ds:Transforms>
              <ds:Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
            </ds:Transforms>
            <ds:DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>
            <ds:DigestValue>Unz40wju5ExcAkTC5PMMJaRxZV8=</ds:DigestValue>
          </ds:Reference>
        </ds:SignedInfo>
        <ds:SignatureValue>kRjlhN3nNNHciRBaD89V89NDZyiKrHV1WcgmQ7mdKyBgXeRMhitnYIt0YsPqwA0DA86doFNiMqfkI4OqcCltUL4VQt3MiMn+6st5R/tQgGoExZ/VE5QqzWjZiJEk8nLcDmqVmOFS8HSsp3cct2rj4PhxBo1IxSo2ZJo0psBg3G4=</ds:SignatureValue>
      </ds:Signature>
    </ws:Security>
    <ns-b3c6b39d:authHeader SOAP:mustUnderstand="true">
      <user><![CDATA[bar]]></user>
    </ns-b3c6b39d:authHeader>
  </SOAP:Header>
</SOAP:Envelope>
';

        $this->assertXmlStringEqualsXmlString($this->cleanXML($expectedString), $this->cleanXML($xml));
    }

    public function testDeSerializeSecurity()
    {

        $headerPlaceholder = new HeaderPlaceholder();

        $this->requestFilter->setSecurityOptionsEncryption(WsSecurityFilterRequest::TOKEN_REFERENCE_SUBJECT_KEY_IDENTIFIER);
        $this->requestFilter->setSecurityOptionsSignature(WsSecurityFilterRequest::TOKEN_REFERENCE_SUBJECT_KEY_IDENTIFIER);

        $keypair1 = new SecurityKeyPair();
        $keypair1->setPrivateKey(\ass\XmlSecurity\Key::RSA_1_5, __DIR__.'/../Fixtures/client_private_key.pem');
        $keypair1->setPublicKey(\ass\XmlSecurity\Key::RSA_1_5, __DIR__.'/../Fixtures/client_public_key.pem');

        $keypair2 = new SecurityKeyPair();
        $keypair2->setPrivateKey(\ass\XmlSecurity\Key::TRIPLEDES_CBC, str_repeat("1", 24));
        $keypair2->setPublicKey(\ass\XmlSecurity\Key::RSA_1_5, __DIR__.'/../Fixtures/server_public_key.pem');

        $keypair3 = new SecurityKeyPair();
        $keypair3->setPrivateKey(\ass\XmlSecurity\Key::RSA_1_5, __DIR__.'/../Fixtures/server_private_key.pem');

        $keypair4 = new SecurityKeyPair();
        $keypair4->setPublicKey(\ass\XmlSecurity\Key::RSA_1_5, __DIR__.'/../Fixtures/client_public_key.pem', true);

        $this->requestFilter->setUserSecurityKeyObject($keypair1);
        $this->requestFilter->setServiceSecurityKeyObject($keypair2);

        $this->responseFilter->setServiceSecurityKeyObject($keypair4);
        $this->responseFilter->setUserSecurityKeyObject($keypair3);

        $security = new Security();
        $security->setUsername('foo');

        $this->headerHandler->addHeaderData($headerPlaceholder, new MustUnderstandHeader($security));


        $env = new RequestHeaderInput();
        $env->setHeader($headerPlaceholder);

        $body = new \Ex\SoapParts\RequestHeaderInput();
        $p = new \Ex\RequestHeader();
        $p->setIn("sss");
        $body->setParameters($p);
        $env->setBody($body);

        $xmlString = $this->serializer->serialize($env, 'xml');

        /**
         * @var $object RequestHeaderInput
         */
        $object = $this->serializer->deserialize($xmlString, RequestHeaderInput::class, 'xml');

        $this->assertEquals("sss", $object->getBody()->getParameters()->getIn());
        $this->assertInstanceOf('Ex\RequestHeader', $object->getBody()->getParameters());
    }

    public function testSerializeSecurityWithTokenTypes()
    {
        $headerPlaceholder = new HeaderPlaceholder();

        $this->requestFilter->setSecurityOptionsEncryption(WsSecurityFilterRequest::TOKEN_REFERENCE_SECURITY_TOKEN);
        $this->requestFilter->setSecurityOptionsSignature(WsSecurityFilterRequest::TOKEN_REFERENCE_SECURITY_TOKEN);

        $security = new Security();
        $security->setUsername('foo');
        $security->setPassword('pass');

        $this->headerHandler->addHeaderData($headerPlaceholder, new MustUnderstandHeader($security));

        $env = new RequestHeaderInput();
        $env->setHeader($headerPlaceholder);

        $body = new \Ex\SoapParts\RequestHeaderInput();
        $p = new \Ex\RequestHeader();
        $p->setIn("sss");
        $body->setParameters($p);
        $env->setBody($body);

        $xml = $this->serializer->serialize($env, 'xml');

        $expectedString = '<?xml version="1.0" encoding="UTF-8"?>
<SOAP:Envelope xmlns:SOAP="http://www.w3.org/2003/05/soap-envelope" xmlns:ws="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:ns-b3c6b39d="http://www.example.org/test/" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xenc="http://www.w3.org/2001/04/xmlenc#">
  <SOAP:Body wsu:Id="Id-70a326e2-488b-48ce-9448-691fe6c0c03b">
    <xenc:EncryptedData Id="Id-1016ff83-2598-4b9b-a7f9-b371170cb2d9" Type="http://www.w3.org/2001/04/xmlenc#Content">
      <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#tripledes-cbc"/>
      <ds:KeyInfo xmlns="http://www.w3.org/2000/09/xmldsig#" xmlns:default="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
        <ws:SecurityTokenReference xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
          <ws:Reference URI="#EncKey-00c08775-01f8-4ca5-90f5-e931cd0b44ad"/>
        </ws:SecurityTokenReference>
      </ds:KeyInfo>
      <xenc:CipherData>
        <xenc:CipherValue>RuqNIV9tlKbV/XBoB0vxN5DjvCuVtJArIgSKcOQ8LoS7CEuD3mbmHsbDwAf3nEWQ7zRrQTw2C3XC+J5xNrMpyaAmiJuSea4TdhhSJ0uMFgtKY9OpSVEAEvFklx7kaOJTxTg+M1DMopAljldbRFVlghmTR0g2PIPaXhJfawO6HbjIeAJ8BfYblDnzoHv6CzpbaRJCKcbi1PjlLSaub1kQtjdONWv5VfLh1jB7M+EgWKGVbjLG6Xq1cColAo24NaD1TI+deaNKT4/Rw3w3zJKnxbEIQ01xgCtiB6AfR6G9851FXqR0OdcqHhVe0vfnjT+NGDEwazgLX6XcnwFDkge7zJ/vqKHWC1Jyjl2ym/P3JTSjje/ozFPNnQjlMVSAK0c5wA57+HsA3OCo9fHWtQoaRw7cboMOg0Qg6IoUi2+g0OhWgC0EKVnUqQzFGqDEXD9S3gIsv7Q+jCpwFEcKvkYe287VgeKmePOqbBLuA5LcMRYDMoOGtY0CUpe0A1klC2cGIYn2EMpGcTK4+ckUp5Hz02JgK6VnaI54f8EaVIV2hhrnnJSes+3/94v3erytbY6pROvM6TzFUDc5dOD+ZU07MfIPZAhV0h0dXmpObiyctpw=</xenc:CipherValue>
      </xenc:CipherData>
    </xenc:EncryptedData>
  </SOAP:Body>
  <SOAP:Header>
    <ws:Security SOAP:mustUnderstand="true">
      <wsu:Timestamp wsu:Id="Id-5b2ad25c-7051-44ec-817d-2ebdb584a0cd">
        <wsu:Created>2016-12-21T14:00:49.000Z</wsu:Created>
        <wsu:Expires>2016-12-21T14:05:49.000Z</wsu:Expires>
      </wsu:Timestamp>
      <ws:UsernameToken wsu:Id="Id-0912147c-da8f-4cc5-a41f-fbd91e6164be">
        <ws:Username>foo</ws:Username>
      </ws:UsernameToken>
      <ws:BinarySecurityToken EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary" ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3" wsu:Id="CertId-aa758993-9d6b-4ac3-9787-dc5d75eb3c4d"/>
      <xenc:EncryptedKey Id="EncKey-00c08775-01f8-4ca5-90f5-e931cd0b44ad">
        <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#rsa-1_5"/>
        <ds:KeyInfo>
          <ws:SecurityTokenReference xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <ws:Reference URI="#EncKey-00c08775-01f8-4ca5-90f5-e931cd0b44ad" ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3"/>
          </ws:SecurityTokenReference>
        </ds:KeyInfo>
        <xenc:CipherData>
          <xenc:CipherValue>lWbYQaXg8hhPxjyMlBMCt0dsxt3NWFZWIlNL3lq6s1qsvQ+UN8Gn1jJYivPkLTtiGKatTtwQijS8iBcOmyp73/27EkGwLqovbhnkz+ZmWdyIcFtdD2qpcb2HD10cppo0a8QPmwEtXIYPM5GlH8kLXm4u9kn9OJA5FreCFASCwT0=</xenc:CipherValue>
        </xenc:CipherData>
        <xenc:ReferenceList>
          <xenc:DataReference URI="#Id-1016ff83-2598-4b9b-a7f9-b371170cb2d9"/>
        </xenc:ReferenceList>
      </xenc:EncryptedKey>
      <ds:Signature>
        <ds:SignedInfo>
          <ds:CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
          <ds:SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"/>
          <ds:Reference URI="#Id-70a326e2-488b-48ce-9448-691fe6c0c03b">
            <ds:Transforms>
              <ds:Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
            </ds:Transforms>
            <ds:DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>
            <ds:DigestValue>RHWAhMuWzw2uUF5cBc6ObCBpC9I=</ds:DigestValue>
          </ds:Reference>
          <ds:Reference URI="#Id-5b2ad25c-7051-44ec-817d-2ebdb584a0cd">
            <ds:Transforms>
              <ds:Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
            </ds:Transforms>
            <ds:DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>
            <ds:DigestValue>CaIRD27prYA1YCyY4fUV5L9rj7Y=</ds:DigestValue>
          </ds:Reference>
          <ds:Reference URI="#Id-0912147c-da8f-4cc5-a41f-fbd91e6164be">
            <ds:Transforms>
              <ds:Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
            </ds:Transforms>
            <ds:DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>
            <ds:DigestValue>Mbbxwz6mcQhxMbiw8RIKHqyXeHs=</ds:DigestValue>
          </ds:Reference>
        </ds:SignedInfo>
        <ds:SignatureValue>F8aOS40ypNHMHnsWDbkWb27NAEezKV7RmodKFuXjmkRB70YKoGUMIZSyvIlaj/E6GQ7a27gQyv6Kjzb8XTemQw+I8kgMGXiECVplbhuH3OayOKvMXPo8g4y0+7P5uf7h0OhFoq9U2Pbya3HDqsxVm5AUdl5AAm9SZkNLoC43tuc=</ds:SignatureValue>
        <ds:KeyInfo>
          <ws:SecurityTokenReference xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <ws:Reference URI="#CertId-aa758993-9d6b-4ac3-9787-dc5d75eb3c4d" ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3"/>
          </ws:SecurityTokenReference>
        </ds:KeyInfo>
      </ds:Signature>
    </ws:Security>
  </SOAP:Header>
</SOAP:Envelope>
';

        $this->assertXmlStringEqualsXmlString($this->cleanXML($expectedString), $this->cleanXML($xml));
    }

    public function testSerializeSecurityWithNoCerts()
    {
        $headerPlaceholder = new HeaderPlaceholder();

        $this->requestFilter->setServiceSecurityKeyObject(null);
        $this->requestFilter->setUserSecurityKeyObject(null);

        $security = new Security();
        $security->setUsername('foo');
        $security->setPassword('pass');

        $this->headerHandler->addHeaderData($headerPlaceholder, new MustUnderstandHeader($security));

        $env = new RequestHeaderInput();
        $env->setHeader($headerPlaceholder);

        $body = new \Ex\SoapParts\RequestHeaderInput();
        $p = new \Ex\RequestHeader();
        $p->setIn("sss");
        $body->setParameters($p);
        $env->setBody($body);

        $xml = $this->serializer->serialize($env, 'xml');

        $expectedString = '<?xml version="1.0" encoding="UTF-8"?>
        <SOAP:Envelope xmlns:SOAP="http://www.w3.org/2003/05/soap-envelope" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" xmlns:ns-b3c6b39d="http://www.example.org/test/" xmlns:ws="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xenc="http://www.w3.org/2001/04/xmlenc#">
          <SOAP:Body>
            <ns-b3c6b39d:requestHeader xmlns:ns-b3c6b39d="http://www.example.org/test/">
              <in><![CDATA[sss]]></in>
            </ns-b3c6b39d:requestHeader>
          </SOAP:Body>
          <SOAP:Header>
            <ws:Security SOAP:mustUnderstand="true">
              <wsu:Timestamp>
                <wsu:Created>2016-12-21T09:51:41.000Z</wsu:Created>
                <wsu:Expires>2016-12-21T09:56:41.000Z</wsu:Expires>
              </wsu:Timestamp>
              <ws:UsernameToken>
                <ws:Username>foo</ws:Username>
                <ws:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordDigest">E+rwHP8u/HO0gP/9gHweD4hm/Lk=</ws:Password>
                <ws:Nonce>MTg3NzQxNjUzMw==</ws:Nonce>
                <wsu:Created>2016-12-21T09:51:41.000Z</wsu:Created>
              </ws:UsernameToken>
            </ws:Security>
          </SOAP:Header>
        </SOAP:Envelope>';

        $this->assertXmlStringEqualsXmlString($this->cleanXML($expectedString), $this->cleanXML($xml));
    }

    public function cleanXML($xml)
    {
        $xml = preg_replace('~(Id|EncKey|Cert|\#)[a-f0-9\-]+~', 'X', $xml);
        $xml = preg_replace('~\d{4}\-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}.*?Z~', 'X', $xml);

        $tags = ['ds:SignatureValue', 'ds:DigestValue', 'xenc:CipherValue', 'ws:BinarySecurityToken', 'ws:Nonce', 'ws:Password'];
        foreach ($tags as $tag) {
            $xml = preg_replace("~(<$tag.*?>)(.*?)(<\\/$tag>)~", '\1abc\3', $xml);
        }
        return $xml;
    }
}
