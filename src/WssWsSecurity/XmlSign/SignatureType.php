<?php

namespace GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign;

/**
 * Class representing SignatureType
 *
 *
 * XSD Type: SignatureType
 */
class SignatureType
{

    /**
     * @property string $id
     */
    private $id = null;

    /**
     * @property
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\SignedInfo
     * $signedInfo
     */
    private $signedInfo = null;

    /**
     * @property
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\SignatureValue
     * $signatureValue
     */
    private $signatureValue = null;

    /**
     * @property
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\KeyInfo
     * $keyInfo
     */
    private $keyInfo = null;

    /**
     * @property
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\ObjectXsd[]
     * $object
     */
    private $object = 'array()';

    /**
     * Gets as id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets a new id
     *
     * @param string $id
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Gets as signedInfo
     *
     * @return
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\SignedInfo
     */
    public function getSignedInfo()
    {
        return $this->signedInfo;
    }

    /**
     * Sets a new signedInfo
     *
     * @param
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\SignedInfo
     * $signedInfo
     * @return self
     */
    public function setSignedInfo(\GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\SignedInfo $signedInfo)
    {
        $this->signedInfo = $signedInfo;
        return $this;
    }

    /**
     * Gets as signatureValue
     *
     * @return
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\SignatureValue
     */
    public function getSignatureValue()
    {
        return $this->signatureValue;
    }

    /**
     * Sets a new signatureValue
     *
     * @param
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\SignatureValue
     * $signatureValue
     * @return self
     */
    public function setSignatureValue(\GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\SignatureValue $signatureValue)
    {
        $this->signatureValue = $signatureValue;
        return $this;
    }

    /**
     * Gets as keyInfo
     *
     * @return \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\KeyInfo
     */
    public function getKeyInfo()
    {
        return $this->keyInfo;
    }

    /**
     * Sets a new keyInfo
     *
     * @param \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\KeyInfo
     * $keyInfo
     * @return self
     */
    public function setKeyInfo(\GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\KeyInfo $keyInfo)
    {
        $this->keyInfo = $keyInfo;
        return $this;
    }

    /**
     * Adds as object
     *
     * @return self
     * @param
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\ObjectXsd
     * $object
     */
    public function addToObject(\GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\ObjectXsd $object)
    {
        $this->object[] = $object;
        return $this;
    }

    /**
     * isset object
     *
     * @param scalar $index
     * @return boolean
     */
    public function issetObject($index)
    {
        return isset($this->object[$index]);
    }

    /**
     * unset object
     *
     * @param scalar $index
     * @return void
     */
    public function unsetObject($index)
    {
        unset($this->object[$index]);
    }

    /**
     * Gets as object
     *
     * @return
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\ObjectXsd[]
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Sets a new object
     *
     * @param
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\ObjectXsd[]
     * $object
     * @return self
     */
    public function setObject(array $object)
    {
        $this->object = $object;
        return $this;
    }


}

