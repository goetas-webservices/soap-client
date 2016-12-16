<?php

namespace GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Secext;

/**
 * Class representing TransformationParametersType
 *
 * This complexType defines a container for elements to be specified from any
 * namespace as properties/parameters of a DSIG transformation.
 * XSD Type: TransformationParametersType
 */
class TransformationParametersType
{

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

