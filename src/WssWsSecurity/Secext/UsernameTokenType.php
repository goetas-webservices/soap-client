<?php

namespace GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Secext;

/**
 * Class representing UsernameTokenType
 *
 * This type represents a username token per Section 4.1
 * XSD Type: UsernameTokenType
 */
class UsernameTokenType
{

    /**
     * @property string $id
     */
    private $id = null;

    /**
     * @property mixed[] $anyAttribute
     */
    private $anyAttribute = array(
        
    );

    /**
     * @property
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Secext\AttributedStringType
     * $username
     */
    private $username = null;

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
     * Gets as username
     *
     * @return
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Secext\AttributedStringType
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Sets a new username
     *
     * @param
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Secext\AttributedStringType
     * $username
     * @return self
     */
    public function setUsername(\GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Secext\AttributedStringType $username)
    {
        $this->username = $username;
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

