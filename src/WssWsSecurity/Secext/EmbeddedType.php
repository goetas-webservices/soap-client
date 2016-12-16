<?php

namespace GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Secext;

/**
 * Class representing EmbeddedType
 *
 * This type represents a reference to an embedded security token.
 * XSD Type: EmbeddedType
 */
class EmbeddedType
{

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
     * @property mixed[] $anyElement
     */
    private $anyElement = array(
        
    );

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

    /**
     * Adds as array
     *
     * @return self
     * @param mixed $array
     */
    public function addToAnyElement($array)
    {
        $this->anyElement[] = $array;
        return $this;
    }

    /**
     * isset anyElement
     *
     * @param scalar $index
     * @return boolean
     */
    public function issetAnyElement($index)
    {
        return isset($this->anyElement[$index]);
    }

    /**
     * unset anyElement
     *
     * @param scalar $index
     * @return void
     */
    public function unsetAnyElement($index)
    {
        unset($this->anyElement[$index]);
    }

    /**
     * Gets as anyElement
     *
     * @return mixed[]
     */
    public function getAnyElement()
    {
        return $this->anyElement;
    }

    /**
     * Sets a new anyElement
     *
     * @param mixed[] $anyElement
     * @return self
     */
    public function setAnyElement(array $anyElement)
    {
        $this->anyElement = $anyElement;
        return $this;
    }


}

