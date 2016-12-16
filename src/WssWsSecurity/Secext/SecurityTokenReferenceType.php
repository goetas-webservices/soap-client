<?php

namespace GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Secext;

/**
 * Class representing SecurityTokenReferenceType
 *
 * This type is used reference a security token.
 * XSD Type: SecurityTokenReferenceType
 */
class SecurityTokenReferenceType
{

    /**
     * @property string $id
     */
    private $id = null;

    /**
     * @property string[] $usage
     */
    private $usage = null;

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
     * Adds as usage
     *
     * @return self
     * @param string $usage
     */
    public function addToUsage($usage)
    {
        $this->usage[] = $usage;
        return $this;
    }

    /**
     * isset usage
     *
     * @param scalar $index
     * @return boolean
     */
    public function issetUsage($index)
    {
        return isset($this->usage[$index]);
    }

    /**
     * unset usage
     *
     * @param scalar $index
     * @return void
     */
    public function unsetUsage($index)
    {
        unset($this->usage[$index]);
    }

    /**
     * Gets as usage
     *
     * @return string[]
     */
    public function getUsage()
    {
        return $this->usage;
    }

    /**
     * Sets a new usage
     *
     * @param string[] $usage
     * @return self
     */
    public function setUsage(array $usage)
    {
        $this->usage = $usage;
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

