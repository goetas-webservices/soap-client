<?php

namespace GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Utility;

/**
 * Class representing TimestampType
 *
 * This complex type ties together the timestamp related elements into a composite
 * type.
 * XSD Type: TimestampType
 */
class TimestampType
{

    /**
     * @property string $id
     */
    private $id = null;

    /**
     * @property
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Utility\Created
     * $created
     */
    private $created = null;

    /**
     * @property
     * \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Utility\Expires
     * $expires
     */
    private $expires = null;

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
     * Gets as created
     *
     * @return \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Utility\Created
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Sets a new created
     *
     * @param \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Utility\Created
     * $created
     * @return self
     */
    public function setCreated(\GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Utility\Created $created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * Gets as expires
     *
     * @return \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Utility\Expires
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * Sets a new expires
     *
     * @param \GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Utility\Expires
     * $expires
     * @return self
     */
    public function setExpires(\GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Utility\Expires $expires)
    {
        $this->expires = $expires;
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

