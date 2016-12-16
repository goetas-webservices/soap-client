<?php

namespace GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign;

/**
 * Class representing TransformType
 *
 *
 * XSD Type: TransformType
 */
class TransformType
{

    /**
     * @property string $algorithm
     */
    private $algorithm = null;

    /**
     * @property string[] $xPath
     */
    private $xPath = 'array()';

    /**
     * @property mixed[] $anyElement
     */
    private $anyElement = array(
        
    );

    /**
     * Gets as algorithm
     *
     * @return string
     */
    public function getAlgorithm()
    {
        return $this->algorithm;
    }

    /**
     * Sets a new algorithm
     *
     * @param string $algorithm
     * @return self
     */
    public function setAlgorithm($algorithm)
    {
        $this->algorithm = $algorithm;
        return $this;
    }

    /**
     * Adds as xPath
     *
     * @return self
     * @param string $xPath
     */
    public function addToXPath($xPath)
    {
        $this->xPath[] = $xPath;
        return $this;
    }

    /**
     * isset xPath
     *
     * @param scalar $index
     * @return boolean
     */
    public function issetXPath($index)
    {
        return isset($this->xPath[$index]);
    }

    /**
     * unset xPath
     *
     * @param scalar $index
     * @return void
     */
    public function unsetXPath($index)
    {
        unset($this->xPath[$index]);
    }

    /**
     * Gets as xPath
     *
     * @return string[]
     */
    public function getXPath()
    {
        return $this->xPath;
    }

    /**
     * Sets a new xPath
     *
     * @param string[] $xPath
     * @return self
     */
    public function setXPath(array $xPath)
    {
        $this->xPath = $xPath;
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

