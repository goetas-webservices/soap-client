<?php

namespace GoetasWebservices\SoapServices\SoapClient\WssWsSecurity;

use ass\XmlSecurity\Key as XmlSecurityKey;

class SecurityKeyPair
{
    /**
     * Private key.
     *
     * @var \ass\XmlSecurity\Key
     */
    private $privateKey = null;

    /**
     * Public key.
     *
     * @var \ass\XmlSecurity\Key
     */
    private $publicKey = null;

    /**
     * Add private key.
     *
     * @param string  $encryptionType Encryption type
     * @param string  $key            Private key
     * @param boolean $keyIsFile      Given key parameter is path to key file
     * @param string  $passphrase     Passphrase for key
     *
     * @return void
     */
    public function setPrivateKey($encryptionType, $key = null, $keyIsFile = true, $passphrase = null)
    {
        $this->privateKey = XmlSecurityKey::factory($encryptionType, $key, $keyIsFile, XmlSecurityKey::TYPE_PRIVATE, $passphrase);
    }

    /**
     * Add public key.
     *
     * @param string  $encryptionType Encryption type
     * @param string  $key            Public key
     * @param boolean $keyIsFile      Given key parameter is path to key file
     *
     * @return void
     */
    public function setPublicKey($encryptionType, $key = null, $keyIsFile = true)
    {
        $this->publicKey = XmlSecurityKey::factory($encryptionType, $key, $keyIsFile, XmlSecurityKey::TYPE_PUBLIC);
    }

    /**
     * Get private key.
     *
     * @return \ass\XmlSecurity\Key
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * Get public key.
     *
     * @return \ass\XmlSecurity\Key
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * Has private and public key?
     *
     * @return boolean
     */
    public function hasKeys()
    {
        return null !== $this->privateKey && null !== $this->publicKey;
    }

    /**
     * Has private key?
     *
     * @return boolean
     */
    public function hasPrivateKey()
    {
        return null !== $this->privateKey;
    }

    /**
     * Has public key?
     *
     * @return boolean
     */
    public function hasPublicKey()
    {
        return null !== $this->publicKey;
    }
}
