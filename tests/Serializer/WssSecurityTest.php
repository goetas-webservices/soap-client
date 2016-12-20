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

        $expectedString = '<?xml version="1.0" encoding="UTF-8"?>
<SOAP:Envelope xmlns:SOAP="http://www.w3.org/2003/05/soap-envelope" xmlns:wssu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" xmlns:ns-b3c6b39d="http://www.example.org/test/" xmlns:wsss="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:dsig="http://www.w3.org/2000/09/xmldsig#" xmlns:xenc="http://www.w3.org/2001/04/xmlenc#">
  <SOAP:Body wssu:Id="Id-a6021715-833e-4c5a-97b5-72cfc22a69dd">
    <xenc:EncryptedData Id="Id-52c598cd-feab-4367-aed7-a32374da65f3" Type="http://www.w3.org/2001/04/xmlenc#Content">
      <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#tripledes-cbc"/>
      <dsig:KeyInfo xmlns="http://www.w3.org/2000/09/xmldsig#" xmlns:default="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
        <wsss:SecurityTokenReference xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
          <wsss:Reference URI="#EncKey-e68e350e-496b-413f-b031-241a3c44e9e0"/>
        </wsss:SecurityTokenReference>
      </dsig:KeyInfo>
      <xenc:CipherData>
        <xenc:CipherValue>wZt0/BXw53bz2Ni1WI1U4UhzPP8jlVPW7K29/wDZDm9/j/KA4D7e2GaAH4GPHSYZ2foadJKSWPaqJP+RsATV8LpGSeLLCoFjx2aYI371xFQu1/SJRuaRKpW8YKYarISSEWcPpVcAjgGBz8rRqoXCd6CZ8XmTTt4AIvXdiQfqsKyBoFYyGsItdoA3qWqXix1/Or1K/MHMd22NC6wJRdLksV+m/ulzbm+NdKvVomGCOxAaGtvk+buCLLskqXo+mQd4Ox2c8Qabh3MPz8tdmabq1JI7pmgx0HVaZS2VJTvimcwCJ1v90AJFz/78ue61FI74DSGCoYGOHnAttWQTcZxJpCqVLbPFjLm7i2an+OAVhD5cdObxi/NsPAzVMIM+fWaN0+r7xCeNplukj5Q40UH7V8jLKAn3PwEgIs2ld+Dn6BYbfkxqvIhEQwjyfm9pwWsEs1H4EY0YqAWtR91Ioxr/ZU1ojjtrBXc56VRB327gLKPnecDEQaycAVKK4EnnbOAldfkbS99MxrwBkADSsVgfQEar5ZhWuA7nD+HphvRjDJmpO1NuWeMBLOL7SuFMZBn5uRvO4G8plkC9x2gOVCRRRMTLHUUB2CLTS7h6ETHrwOI=</xenc:CipherValue>
      </xenc:CipherData>
    </xenc:EncryptedData>
  </SOAP:Body>
  <SOAP:Header>
    <wsss:Security SOAP:mustUnderstand="true">
      <wssu:Timestamp xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" xmlns:default="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" wssu:Id="Id-74fe7c35-168c-433f-b9d3-9c78d50643c9">
        <wssu:Created>2016-12-20T18:12:12.000Z</wssu:Created>
        <wssu:Expires>2016-12-20T18:17:12.000Z</wssu:Expires>
      </wssu:Timestamp>
      <wsss:UsernameToken xmlns:wssu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" wssu:Id="Id-4b909fb8-2702-4d37-b199-b670f2a654fb">
        <wsss:Username>foo</wsss:Username>
      </wsss:UsernameToken>
      <wsss:BinarySecurityToken xmlns:wssu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary" ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3" wssu:Id="CertId-ae9a98aa-5ccf-42a4-be59-814cd46fe3c7">MIICoDCCAgkCBEnhw2IwDQYJKoZIhvcNAQEFBQAwgZYxCzAJBgNVBAYTAk5aMRMwEQYDVQQIEwpXZWxsaW5ndG9uMRowGAYDVQQHExFQYXJhcGFyYXVtdSBCZWFjaDEqMCgGA1UEChMhU29zbm9za2kgU29mdHdhcmUgQXNzb2NpYXRlcyBMdGQuMRAwDgYDVQQLEwdVbmtub3duMRgwFgYDVQQDEw9EZW5uaXMgU29zbm9za2kwHhcNMDkwNDEyMTAzMzA2WhcNMzYwODI3MTAzMzA2WjCBljELMAkGA1UEBhMCTloxEzARBgNVBAgTCldlbGxpbmd0b24xGjAYBgNVBAcTEVBhcmFwYXJhdW11IEJlYWNoMSowKAYDVQQKEyFTb3Nub3NraSBTb2Z0d2FyZSBBc3NvY2lhdGVzIEx0ZC4xEDAOBgNVBAsTB1Vua25vd24xGDAWBgNVBAMTD0Rlbm5pcyBTb3Nub3NraTCBnzANBgkqhkiG9w0BAQEFAAOBjQAwgYkCgYEAhOVyNK8xyxtb4DnKtU6mF9KoiFqCk7eKoLE26+9h410CtTkxzWAfgnR+8i+LPbdsPY+yXAo6NYpCCKolXfDLe+AG2GwnMZGrIl6+BLF3hqTmIXBFTLGUmC7A7uBTivaWgdH1w3hb33rASoVU67BVtQ3QQi99juZX4vU9o9pScocCAwEAATANBgkqhkiG9w0BAQUFAAOBgQBMNPo1KAGbz8Jl6HGbtAcetieSJ3bEAXmv1tcjysBS67AXzdu1Ac+onHh2EpzBM7kuGbw+trU+AhulooPpewIQRApXP1F0KHRDcbqWjwvknS6HnomN9572giLGKn2601bHiRUj35hiA8aLmMUBppIRPFFAoQ0QUBCPx+m8/0n33w==</wsss:BinarySecurityToken>
      <xenc:EncryptedKey xmlns:xenc="http://www.w3.org/2001/04/xmlenc#" Id="EncKey-e68e350e-496b-413f-b031-241a3c44e9e0">
        <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#rsa-1_5"/>
        <xenc:CipherData>
          <xenc:CipherValue>e4q/136uZJjLsvgqBz93BLgtHHgg7CBkJ8XdezFfsu2nBLtm5Z27499rUJafreJ8FNZC2VxZAb7aaTWX1gOU/eAEq4KA2pmw0ZlsEZo+wubzc0ncPrQYRadKYiFFGt543Wx3LLd+A68T38JUbUNv0DjSEiVC3+FHvVuaOOttydE=</xenc:CipherValue>
        </xenc:CipherData>
        <xenc:ReferenceList>
          <xenc:DataReference URI="#Id-52c598cd-feab-4367-aed7-a32374da65f3"/>
        </xenc:ReferenceList>
      </xenc:EncryptedKey>
      <dsig:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
        <dsig:SignedInfo>
          <dsig:CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
          <dsig:SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"/>
          <dsig:Reference URI="#Id-a6021715-833e-4c5a-97b5-72cfc22a69dd">
            <dsig:Transforms>
              <dsig:Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
            </dsig:Transforms>
            <dsig:DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>
            <dsig:DigestValue>Gh5gQXLbHDOZm/x5mpOrhOid3zE=</dsig:DigestValue>
          </dsig:Reference>
          <dsig:Reference URI="#Id-74fe7c35-168c-433f-b9d3-9c78d50643c9">
            <dsig:Transforms>
              <dsig:Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
            </dsig:Transforms>
            <dsig:DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>
            <dsig:DigestValue>2jmj7l5rSw0yVb/vlWAYkK/YBwk=</dsig:DigestValue>
          </dsig:Reference>
          <dsig:Reference URI="#Id-4b909fb8-2702-4d37-b199-b670f2a654fb">
            <dsig:Transforms>
              <dsig:Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
            </dsig:Transforms>
            <dsig:DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>
            <dsig:DigestValue>2jmj7l5rSw0yVb/vlWAYkK/YBwk=</dsig:DigestValue>
          </dsig:Reference>
        </dsig:SignedInfo>
        <dsig:SignatureValue>cslBnIZq7zBc7F0ZQa1zXrhsTA6S6mcpDLIdpPZgWCsN4VqmWQrgJCCkcoxoUSTqYeeJfHdnGiG3//hPFidEIMkWQKzgxzMIzskTXNuA6dPcQ58w6euAVgWrwhetcXzGtk/iVNHSaoHGEsGJjubt1d7PbuTo7j/Ov4VXWilKCSw=</dsig:SignatureValue>
      </dsig:Signature>
    </wsss:Security>
    <ns-b3c6b39d:authHeader SOAP:mustUnderstand="true">
      <user><![CDATA[bar]]></user>
    </ns-b3c6b39d:authHeader>
  </SOAP:Header>
</SOAP:Envelope>';

        $this->assertXmlStringEqualsXmlString($this->cleanXML($expectedString), $this->cleanXML($xml));
    }

    public function cleanXML($xml)
    {
        $xml = preg_replace('~(Id|EncKey|Cert|\#)[a-f0-9\-]+~', '', $xml);
        $xml = preg_replace('~\d{4}\-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}.*Z~', '', $xml);

        $tags = ['dsig:SignatureValue', 'dsig:DigestValue', 'xenc:CipherValue'];
        foreach ($tags as $tag) {
            $xml = preg_replace("~<$tag>.*?<\\/$tag>~", '', $xml);
        }
        return $xml;
    }
}
