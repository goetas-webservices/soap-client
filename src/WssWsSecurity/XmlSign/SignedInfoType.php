<?php

namespace GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign;

/**
 * Class representing SignedInfoType
 *
 *
 * XSD Type: SignedInfoType
 */
class SignedInfoType
{

    /**
     * @property string $id
     */
    private $id = null;

    /**
     * @property
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\CanonicalizationMethod
     * $canonicalizationMethod
     */
    private $canonicalizationMethod = null;

    /**
     * @property
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\SignatureMethod
     * $signatureMethod
     */
    private $signatureMethod = null;

    /**
     * @property
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\Reference[]
     * $reference
     */
    private $reference = 'array()';

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
     * Gets as canonicalizationMethod
     *
     * @return
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\CanonicalizationMethod
     */
    public function getCanonicalizationMethod()
    {
        return $this->canonicalizationMethod;
    }

    /**
     * Sets a new canonicalizationMethod
     *
     * @param
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\CanonicalizationMethod
     * $canonicalizationMethod
     * @return self
     */
    public function setCanonicalizationMethod(\GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\CanonicalizationMethod $canonicalizationMethod)
    {
        $this->canonicalizationMethod = $canonicalizationMethod;
        return $this;
    }

    /**
     * Gets as signatureMethod
     *
     * @return
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\SignatureMethod
     */
    public function getSignatureMethod()
    {
        return $this->signatureMethod;
    }

    /**
     * Sets a new signatureMethod
     *
     * @param
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\SignatureMethod
     * $signatureMethod
     * @return self
     */
    public function setSignatureMethod(\GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\SignatureMethod $signatureMethod)
    {
        $this->signatureMethod = $signatureMethod;
        return $this;
    }

    /**
     * Adds as reference
     *
     * @return self
     * @param
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\Reference
     * $reference
     */
    public function addToReference(\GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\Reference $reference)
    {
        $this->reference[] = $reference;
        return $this;
    }

    /**
     * isset reference
     *
     * @param scalar $index
     * @return boolean
     */
    public function issetReference($index)
    {
        return isset($this->reference[$index]);
    }

    /**
     * unset reference
     *
     * @param scalar $index
     * @return void
     */
    public function unsetReference($index)
    {
        unset($this->reference[$index]);
    }

    /**
     * Gets as reference
     *
     * @return
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\Reference[]
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Sets a new reference
     *
     * @param
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\Reference[]
     * $reference
     * @return self
     */
    public function setReference(array $reference)
    {
        $this->reference = $reference;
        return $this;
    }


}

