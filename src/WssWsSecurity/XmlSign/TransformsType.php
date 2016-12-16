<?php

namespace GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign;

/**
 * Class representing TransformsType
 *
 *
 * XSD Type: TransformsType
 */
class TransformsType
{

    /**
     * @property
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\Transform[]
     * $transform
     */
    private $transform = 'array()';

    /**
     * Adds as transform
     *
     * @return self
     * @param
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\Transform
     * $transform
     */
    public function addToTransform(\GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\Transform $transform)
    {
        $this->transform[] = $transform;
        return $this;
    }

    /**
     * isset transform
     *
     * @param scalar $index
     * @return boolean
     */
    public function issetTransform($index)
    {
        return isset($this->transform[$index]);
    }

    /**
     * unset transform
     *
     * @param scalar $index
     * @return void
     */
    public function unsetTransform($index)
    {
        unset($this->transform[$index]);
    }

    /**
     * Gets as transform
     *
     * @return
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\Transform[]
     */
    public function getTransform()
    {
        return $this->transform;
    }

    /**
     * Sets a new transform
     *
     * @param
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign\Transform[]
     * $transform
     * @return self
     */
    public function setTransform(array $transform)
    {
        $this->transform = $transform;
        return $this;
    }


}

