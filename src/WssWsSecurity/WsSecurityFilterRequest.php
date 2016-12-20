<?php

namespace GoetasWebservices\SoapServices\SoapClient\WssWsSecurity;

use ass\XmlSecurity\DSig as XmlSecurityDSig;
use ass\XmlSecurity\Enc as XmlSecurityEnc;

class WsSecurityFilterRequest extends AbstractWsSecurityFilter
{
    /**
     * Web Services Security: SOAP Message Security 1.0 (WS-Security 2004)
     */
    const NAME_WSS_SMS = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0';

    /**
     * Web Services Security UsernameToken Profile 1.0
     */
    const NAME_WSS_UTP = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0';

    /**
     * Web Services Security X.509 Certificate Token Profile
     */
    const NAME_WSS_X509 = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0';

    /**
     * The date format to be used with {@link \DateTime}
     */
    const DATETIME_FORMAT = 'Y-m-d\TH:i:s.000\Z';

    /**
     * (X509 3.2.1) Reference to a Subject Key Identifier
     */
    const TOKEN_REFERENCE_SUBJECT_KEY_IDENTIFIER = 0;

    /**
     * (X509 3.2.1) Reference to a Security Token
     */
    const TOKEN_REFERENCE_SECURITY_TOKEN = 1;

    /**
     * (SMS_1.1 7.3) Key Identifiers
     */
    const TOKEN_REFERENCE_THUMBPRINT_SHA1 = 2;

    /**
     * (SMS 10) Add security timestamp.
     *
     * @var boolean
     */
    private $addTimestamp = true;

    /**
     * Encrypt the signature?
     *
     * @var boolean
     */
    private $encryptSignature = false;

    /**
     * (SMS 10) Security timestamp expires time in seconds.
     *
     * @var int
     */
    private $expires = 300;

    /**
     * Sign all headers.
     *
     * @var boolean
     */
    private $signAllHeaders = false;

    /**
     * @var \DateTime
     */
    private $initialTimestamp;

    /**
     * (X509 3.2) Token reference type for encryption.
     *
     * @var int
     */
    private $tokenReferenceEncryption = null;

    /**
     * (X509 3.2) Token reference type for signature.
     *
     * @var int
     */
    private $tokenReferenceSignature = null;


    public function setTimestampOptions($addTimestamp = true, $expires = 300)
    {
        $this->addTimestamp = $addTimestamp;
        $this->expires = $expires;
    }

    /**
     * @param \DateTime $initialTimestamp
     */
    public function __construct(\DateTime $initialTimestamp = null)
    {
        $this->initialTimestamp = $initialTimestamp;
    }

    /**
     * Set security options.
     *
     * @param int $tokenReference self::TOKEN_REFERENCE_SUBJECT_KEY_IDENTIFIER | self::TOKEN_REFERENCE_SECURITY_TOKEN | self::TOKEN_REFERENCE_THUMBPRINT_SHA1
     * @param boolean $encryptSignature Encrypt signature
     *
     * @return void
     */
    public function setSecurityOptionsEncryption($tokenReference, $encryptSignature = false)
    {
        $this->tokenReferenceEncryption = $tokenReference;
        $this->encryptSignature = $encryptSignature;
    }

    /**
     * Set security options.
     *
     * @param int $tokenReference self::TOKEN_REFERENCE_SUBJECT_KEY_IDENTIFIER | self::TOKEN_REFERENCE_SECURITY_TOKEN | self::TOKEN_REFERENCE_THUMBPRINT_SHA1
     * @param boolean $signAllHeaders Sign all headers?
     *
     * @return void
     */
    public function setSecurityOptionsSignature($tokenReference, $signAllHeaders = false)
    {
        $this->tokenReferenceSignature = $tokenReference;
        $this->signAllHeaders = $signAllHeaders;
    }

    /**
     * Adds the configured KeyInfo to the parentNode.
     *
     * @param \DOMDocument $dom
     * @param int $tokenReference Token reference type
     * @param string $guid Unique ID
     * @param XmlSecurityKey $xmlSecurityKey XML security key
     *
     * @return \DOMElement
     */
    private function createKeyInfo(\DOMDocument $dom, $tokenReference, $guid, XmlSecurityKey $xmlSecurityKey = null)
    {
        $keyInfo = $dom->createElementNS(XmlSecurityDSig::NS_XMLDSIG, 'KeyInfo');
        $securityTokenReference = $dom->createElementNS(self::NS_WSS, 'SecurityTokenReference');
        $keyInfo->appendChild($securityTokenReference);
        // security token
        if (self::TOKEN_REFERENCE_SECURITY_TOKEN === $tokenReference) {
            $reference = $dom->createElementNS(self::NS_WSS, 'Reference');
            $reference->setAttribute('URI', '#' . $guid);
            if (null !== $xmlSecurityKey) {
                $reference->setAttribute('ValueType', self::NAME_WSS_X509 . '#X509v3');
            }
            $securityTokenReference->appendChild($reference);
            // subject key identifier
        } elseif (self::TOKEN_REFERENCE_SUBJECT_KEY_IDENTIFIER === $tokenReference && null !== $xmlSecurityKey) {
            $keyIdentifier = $dom->createElementNS(self::NS_WSS, 'KeyIdentifier');
            $keyIdentifier->setAttribute('EncodingType', self::NAME_WSS_SMS . '#Base64Binary');
            $keyIdentifier->setAttribute('ValueType', self::NAME_WSS_X509 . '#509SubjectKeyIdentifier');
            $securityTokenReference->appendChild($keyIdentifier);
            $certificate = $xmlSecurityKey->getX509SubjectKeyIdentifier();
            $dataNode = new \DOMText($certificate);
            $keyIdentifier->appendChild($dataNode);
            // thumbprint sha1
        } elseif (self::TOKEN_REFERENCE_THUMBPRINT_SHA1 === $tokenReference && null !== $xmlSecurityKey) {
            $keyIdentifier = $dom->createElementNS(self::NS_WSS, 'KeyIdentifier');
            $keyIdentifier->setAttribute('EncodingType', self::NAME_WSS_SMS . '#Base64Binary');
            $keyIdentifier->setAttribute('ValueType', self::NAME_WSS_SMS_1_1 . '#ThumbprintSHA1');
            $securityTokenReference->appendChild($keyIdentifier);
            $thumbprintSha1 = base64_encode(sha1(base64_decode($xmlSecurityKey->getX509Certificate(true)), true));
            $dataNode = new \DOMText($thumbprintSha1);
            $keyIdentifier->appendChild($dataNode);
        }

        return $keyInfo;
    }

    /**
     * Create a list of \DOMNodes that should be encrypted.
     *
     * @param \DOMDocument $dom DOMDocument to query
     *
     * @return \DOMNodeList
     */
    private function createNodeListForEncryption(\DOMDocument $dom)
    {
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('SOAP-ENV', $dom->documentElement->namespaceURI);
        $xpath->registerNamespace('ds', XmlSecurityDSig::NS_XMLDSIG);
        if ($this->encryptSignature === true) {
            $query = '//ds:Signature | //SOAP-ENV:Body';
        } else {
            $query = '//SOAP-ENV:Body';
        }

        return $xpath->query($query);
    }

    /**
     * Create a list of \DOMNodes that should be signed.
     *
     * @param \DOMDocument $dom DOMDocument to query
     * @param \DOMElement $security Security element
     *
     * @return array(\DOMNode)
     */
    private function createNodeListForSigning(\DOMDocument $dom, \DOMElement $security)
    {
        $nodes = array();
        $body = $dom->getElementsByTagNameNS($dom->documentElement->namespaceURI, 'Body')->item(0);
        if (null !== $body) {
            $nodes[] = $body;
        }
        foreach ($security->childNodes as $node) {
            if (XML_ELEMENT_NODE === $node->nodeType) {
                $nodes[] = $node;
            }
        }
        if ($this->signAllHeaders) {
            foreach ($security->parentNode->childNodes as $node) {
                if (XML_ELEMENT_NODE === $node->nodeType &&
                    self::NS_WSS !== $node->namespaceURI
                ) {
                    $nodes[] = $node;
                }
            }
        }
        return $nodes;
    }


    /**
     * Modify the given request XML.
     *
     * @param \DOMDocument $dom
     * @param Security $securityData
     *
     * @return \DOMElement
     */
    public function filterDom(\DOMDocument $dom, Security $securityData)
    {
        $security = $dom->createElementNS(self::NS_WSS, 'Security');

        $root = $dom->documentElement;
        $root->setAttributeNS(
            'http://www.w3.org/2000/xmlns/', // xmlns namespace URI
            'xmlns:wssu',
            self::NS_WSU
        );
        $root->setAttributeNS(
            'http://www.w3.org/2000/xmlns/', // xmlns namespace URI
            'xmlns:wsss',
            self::NS_WSS
        );
        $root->setAttributeNS(
            'http://www.w3.org/2000/xmlns/', // xmlns namespace URI
            'xmlns:dsig',
            XmlSecurityDSig::NS_XMLDSIG
        );
        $root->setAttributeNS(
            'http://www.w3.org/2000/xmlns/', // xmlns namespace URI
            'xmlns:xenc',
            XmlSecurityEnc::NS_XMLENC
        );

        // init timestamp
        $dt = $this->initialTimestamp ?: new \DateTime('now', new \DateTimeZone('UTC'));

        if (true === $this->addTimestamp || null !== $this->expires) {
            $this->handleTimestamp($security, $dt);
        }

        if (null !== $securityData->getUsername()) {
            $this->handleUsername($security, $dt, $securityData);
        }

        if (null !== $this->userSecurityKey && $this->userSecurityKey->hasKeys()) {
            $signature = $this->handleSignature($security);

            // encrypt soap document
            if (null !== $this->serviceSecurityKey && $this->serviceSecurityKey->hasKeys()) {
                $this->handleEncryption($security, $signature);
            }
        }
        return $security;
    }

    /**
     * Generate a pseudo-random version 4 UUID.
     *
     * @see http://de.php.net/manual/en/function.uniqid.php#94959
     *
     * @return string
     */
    private static function generateUUID()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * @param \DOMElement $security
     * @param \DateTime $dt
     */
    private function handleTimestamp(\DOMElement $security, \DateTime $dt)
    {
        $dom = $security->ownerDocument;
        $timestamp = $dom->createElementNS(self::NS_WSU, 'Timestamp');
        $created = $dom->createElementNS(self::NS_WSU, 'Created', $dt->format(self::DATETIME_FORMAT));
        $timestamp->appendChild($created);
        if (null !== $this->expires) {
            $dt = clone $dt;
            $dt->modify('+' . $this->expires . ' seconds');
            $expiresTimestamp = $dt->format(self::DATETIME_FORMAT);
            $expires = $dom->createElementNS(self::NS_WSU, 'Expires', $expiresTimestamp);
            $timestamp->appendChild($expires);
        }
        $security->appendChild($timestamp);
    }

    /**
     * @param \DOMElement $security
     * @param $dt
     * @param Security $securityData
     */
    private function handleUsername(\DOMElement $security, $dt, Security $securityData)
    {
        $dom = $security->ownerDocument;
        $usernameToken = $dom->createElementNS(self::NS_WSS, 'UsernameToken');
        $security->appendChild($usernameToken);

        $username = $dom->createElementNS(self::NS_WSS, 'Username', $securityData->getUsername());
        $usernameToken->appendChild($username);

        if (null !== $securityData->getPassword()
            && (null === $this->userSecurityKey
                || (null !== $this->userSecurityKey && !$this->userSecurityKey->hasPrivateKey()))
        ) {

            if ($securityData->isPasswordDigest()) {
                $nonce = mt_rand();
                $password = base64_encode(sha1($nonce . $dt->format(self::DATETIME_FORMAT) . $securityData->getPassword(), true));
                $passwordType = self::NAME_WSS_UTP . '#PasswordDigest';
            } else {
                $password = $securityData->getPassword();
                $passwordType = self::NAME_WSS_UTP . '#PasswordText';
            }

            $password = $dom->createElementNS(self::NS_WSS, 'Password', $password);
            $password->setAttribute('Type', $passwordType);
            $usernameToken->appendChild($password);
            if ($securityData->isPasswordDigest()) {
                $nonce = $dom->createElementNS(self::NS_WSS, 'Nonce', base64_encode($nonce));
                $usernameToken->appendChild($nonce);

                $created = $dom->createElementNS(self::NS_WSU, 'Created', $dt->format(self::DATETIME_FORMAT));
                $usernameToken->appendChild($created);
            }
        }
    }

    /**
     * @param \DOMElement $security
     * @return \DOMElement
     */
    private function handleSignature(\DOMElement $security)
    {
        $dom = $security->ownerDocument;
        $guid = 'CertId-' . self::generateUUID();
        // add token references
        $keyInfo = null;
        if (null !== $this->tokenReferenceSignature) {
            $keyInfo = $this->createKeyInfo($dom, $this->tokenReferenceSignature, $guid, $this->userSecurityKey->getPublicKey());
        }
        $nodes = $this->createNodeListForSigning($dom, $security);
        $signature = XmlSecurityDSig::createSignature($this->userSecurityKey->getPrivateKey(), XmlSecurityDSig::EXC_C14N, $security, null, $keyInfo);

        if ((!$prefix = $security->lookupPrefix(self::NS_WSU)) && (!$prefix = $security->ownerDocument->lookupPrefix(self::NS_WSU))) {
            $prefix = 'ns-'.  substr(sha1(self::NS_WSU), 0, 8);
        }

        $options = array(
            'id_ns_prefix' => $prefix ?: 'wsu',
            'id_prefix_ns' => self::NS_WSU,
        );
        foreach ($nodes as $node) {
            XmlSecurityDSig::addNodeToSignature($signature, $node, XmlSecurityDSig::SHA1, XmlSecurityDSig::EXC_C14N, $options);
        }
        XmlSecurityDSig::signDocument($signature, $this->userSecurityKey->getPrivateKey(), XmlSecurityDSig::EXC_C14N);

        $publicCertificate = $this->userSecurityKey->getPublicKey()->getX509Certificate(true);
        $binarySecurityToken = $dom->createElementNS(self::NS_WSS, 'BinarySecurityToken', $publicCertificate);
        $binarySecurityToken->setAttribute('EncodingType', self::NAME_WSS_SMS . '#Base64Binary');
        $binarySecurityToken->setAttribute('ValueType', self::NAME_WSS_X509 . '#X509v3');

        $security->insertBefore($binarySecurityToken, $signature);

        $binarySecurityToken->setAttributeNs(self::NS_WSU, $prefix.':Id', $guid);


        return $signature;
    }

    /**
     * @param \DOMElement $security
     * @param \DOMElement $signature
     */
    private function handleEncryption(\DOMElement $security, \DOMElement $signature)
    {
        $dom = $security->ownerDocument;
        $guid = 'EncKey-' . self::generateUUID();
        // add token references
        $keyInfo = null;
        if (null !== $this->tokenReferenceEncryption) {
            $keyInfo = $this->createKeyInfo($dom, $this->tokenReferenceEncryption, $guid, $this->serviceSecurityKey->getPublicKey());
        }
        $encryptedKey = XmlSecurityEnc::createEncryptedKey($guid, $this->serviceSecurityKey->getPrivateKey(), $this->serviceSecurityKey->getPublicKey(), $security, $signature, $keyInfo);
        $referenceList = XmlSecurityEnc::createReferenceList($encryptedKey);
        // token reference to encrypted key
        $keyInfo = $this->createKeyInfo($dom, self::TOKEN_REFERENCE_SECURITY_TOKEN, $guid);
        $nodes = $this->createNodeListForEncryption($dom);
        foreach ($nodes as $node) {
            $type = XmlSecurityEnc::ELEMENT;
            if ($node->localName == 'Body') {
                $type = XmlSecurityEnc::CONTENT;
            }
            XmlSecurityEnc::encryptNode($node, $type, $this->serviceSecurityKey->getPrivateKey(), $referenceList, $keyInfo);
        }
    }
}
