<?php

namespace GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Secext;

/**
 * Class representing ReferenceType
 *
 * This type represents a reference to an external security token.
 * XSD Type: ReferenceType
 */
class ReferenceType
{

    /**
     * @property string $uRI
     */
    private $uRI = null;

    /**
     * @property string $valueType
     */
    private $valueType = null;

    /**
     * @property mixed[] $anyAttribute
     */
    private $anyAttribute = array(
        
    );

    /**
     * Gets as uRI
     *
     * @return string
     */
    public function getURI()
    {
        return $this->uRI;
    }

    /**
     * Sets a new uRI
     *
     * @param string $uRI
     * @return self
     */
    public function setURI($uRI)
    {
        $this->uRI = $uRI;
        return $this;
    }

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

    /**
     * Adds as array
     *
     * @return self
     * @param mixed $array
     */
    public function addToAnyAttribute($array)
    {
        $this->anyAttribute[] = $array;
        return $this;
    }

    /**
     * isset anyAttribute
     *
     * @param scalar $index
     * @return boolean
     */
    public function issetAnyAttribute($index)
    {
        return isset($this->anyAttribute[$index]);
    }

    /**
     * unset anyAttribute
     *
     * @param scalar $index
     * @return void
     */
    public function unsetAnyAttribute($index)
    {
        unset($this->anyAttribute[$index]);
    }

    /**
     * Gets as anyAttribute
     *
     * @return mixed[]
     */
    public function getAnyAttribute()
    {
        return $this->anyAttribute;
    }

    /**
     * Sets a new anyAttribute
     *
     * @param mixed[] $anyAttribute
     * @return self
     */
    public function setAnyAttribute(array $anyAttribute)
    {
        $this->anyAttribute = $anyAttribute;
        return $this;
    }


}

