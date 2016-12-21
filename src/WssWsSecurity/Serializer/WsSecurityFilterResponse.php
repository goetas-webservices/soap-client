<?php

namespace GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Serializer;

use ass\XmlSecurity\DSig as XmlSecurityDSig;
use ass\XmlSecurity\Enc as XmlSecurityEnc;
use ass\XmlSecurity\Key as XmlSecurityKey;
use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Exception\ClientException;

class WsSecurityFilterResponse extends AbstractWsSecurityFilter
{
    /**
     * Gets the referenced node for the given URI.
     *
     * @param \DOMElement $node Node
     * @param string $uri URI
     *
     * @return \DOMElement
     */
    private function getReferenceNodeForUri(\DOMElement $node, $uri)
    {
        $url = parse_url($uri);
        $referenceId = $url['fragment'];
        $query = '//*[@wsu:Id="' . $referenceId . '" or @Id="' . $referenceId . '"]';
        $xpath = new \DOMXPath($node->ownerDocument);
        $xpath->registerNamespace('wsu', self::NS_WSU);

        return $xpath->query($query)->item(0);
    }

    /**
     * Tries to resolve a key from the given \DOMElement.
     *
     * @param \DOMElement $node Node where to resolve the key
     * @param string $algorithm XML security key algorithm
     *
     * @return \ass\XmlSecurity\Key|null
     */
    public function keyInfoSecurityTokenReferenceResolver(\DOMElement $node, $algorithm)
    {
        foreach ($node->childNodes as $key) {
            if (self::NS_WSS === $key->namespaceURI) {
                switch ($key->localName) {
                    case 'KeyIdentifier':

                        return $this->serviceSecurityKey->getPublicKey();
                    case 'Reference':
                        $uri = $key->getAttribute('URI');
                        $referencedNode = $this->getReferenceNodeForUri($node, $uri);

                        if (XmlSecurityEnc::NS_XMLENC === $referencedNode->namespaceURI
                            && 'EncryptedKey' == $referencedNode->localName
                        ) {
                            $key = XmlSecurityEnc::decryptEncryptedKey($referencedNode, $this->userSecurityKey->getPrivateKey());

                            return XmlSecurityKey::factory($algorithm, $key, false, XmlSecurityKey::TYPE_PRIVATE);
                        } elseif (self::NS_WSS === $referencedNode->namespaceURI
                            && 'BinarySecurityToken' == $referencedNode->localName
                        ) {

                            $key = XmlSecurityPem::formatKeyInPemFormat($referencedNode->textContent);

                            return XmlSecurityKey::factory(XmlSecurityKey::RSA_SHA1, $key, false, XmlSecurityKey::TYPE_PUBLIC);
                        }
                }
            }
        }

        return null;
    }


    /**
     * Modify the given request XML.
     *
     * @param \DOMDocument $dom
     *
     * @return void
     */
    public function filterDom(\DOMDocument $dom)
    {
        // locate security header
        $security = $dom->getElementsByTagNameNS(self::NS_WSS, 'Security')->item(0);
        if (null !== $security) {
            // add SecurityTokenReference resolver for KeyInfo
            $keyResolver = array($this, 'keyInfoSecurityTokenReferenceResolver');
            XmlSecurityDSig::addKeyInfoResolver(self::NS_WSS, 'SecurityTokenReference', $keyResolver);
            // do we have a reference list in header
            $referenceList = XmlSecurityEnc::locateReferenceList($security);
            // get a list of encrypted nodes

            $encryptedNodes = XmlSecurityEnc::locateEncryptedData($dom, $referenceList);

            // decrypt them
            if (null !== $encryptedNodes) {

                foreach ($encryptedNodes as $encryptedNode) {
                    XmlSecurityEnc::decryptNode($encryptedNode);
                }
            }
            // locate signature node
            $signature = XmlSecurityDSig::locateSignature($security);
            if (null !== $signature) {
                // verify references
                $options = array(
                    'id_ns_prefix' => 'wsu', // used only for the xpath prefix
                    'id_prefix_ns' => self::NS_WSU
                );
                if (XmlSecurityDSig::verifyReferences($signature, $options) !== true) {
                    throw new ClientException('The node signature or decryption was invalid');
                }
                // verify signature
                if (XmlSecurityDSig::verifyDocumentSignature($signature) !== true) {
                    throw new ClientException('The document signature or decryption was invalid');
                }
            }

            $security->parentNode->removeChild($security);
        }
    }
}
