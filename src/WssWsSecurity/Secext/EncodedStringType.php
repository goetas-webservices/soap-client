<?php

namespace GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Secext;

/**
 * Class representing EncodedStringType
 *
 * This type is used for elements containing stringified binary data.
 * XSD Type: EncodedString
 */
class EncodedStringType extends AttributedStringType
{

    /**
     * @property string $encodingType
     */
    private $encodingType = null;

    /**
     * Gets as encodingType
     *
     * @return string
     */
    public function getEncodingType()
    {
        return $this->encodingType;
    }

    /**
     * Sets a new encodingType
     *
     * @param string $encodingType
     * @return self
     */
    public function setEncodingType($encodingType)
    {
        $this->encodingType = $encodingType;
        return $this;
    }


}

