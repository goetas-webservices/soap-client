<?php

namespace GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Secext;

/**
 * Class representing KeyIdentifierType
 *
 * A security token key identifier
 * XSD Type: KeyIdentifierType
 */
class KeyIdentifierType extends EncodedStringType
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

