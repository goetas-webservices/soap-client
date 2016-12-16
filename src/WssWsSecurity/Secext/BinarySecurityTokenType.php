<?php

namespace GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Secext;

/**
 * Class representing BinarySecurityTokenType
 *
 * A security token that is encoded in binary
 * XSD Type: BinarySecurityTokenType
 */
class BinarySecurityTokenType extends EncodedStringType
{

    /**
     * @property string $valueType
     */
    private $valueType = null;

    /**
     * Gets as valueType
     *
     * @return string
     */
    public function getValueType()
    {
        return $this->valueType;
    }

    /**
     * Sets a new valueType
     *
     * @param string $valueType
     * @return self
     */
    public function setValueType($valueType)
    {
        $this->valueType = $valueType;
        return $this;
    }


}

