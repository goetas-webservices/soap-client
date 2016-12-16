<?php

namespace GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign;

/**
 * Class representing SPKIDataType
 *
 *
 * XSD Type: SPKIDataType
 */
class SPKIDataType
{

    /**
     * @property mixed[] $sPKISexp
     */
    private $sPKISexp = 'array()';

    /**
     * @property mixed[] $anyElement
     */
    private $anyElement = array(
        
    );

    /**
     * Adds as sPKISexp
     *
     * @return self
     * @param mixed $sPKISexp
     */
    public function addToSPKISexp($sPKISexp)
    {
        $this->sPKISexp[] = $sPKISexp;
        return $this;
    }

    /**
     * isset sPKISexp
     *
     * @param scalar $index
     * @return boolean
     */
    public function issetSPKISexp($index)
    {
        return isset($this->sPKISexp[$index]);
    }

    /**
     * unset sPKISexp
     *
     * @param scalar $index
     * @return void
     */
    public function unsetSPKISexp($index)
    {
        unset($this->sPKISexp[$index]);
    }

    /**
     * Gets as sPKISexp
     *
     * @return mixed[]
     */
    public function getSPKISexp()
    {
        return $this->sPKISexp;
    }

    /**
     * Sets a new sPKISexp
     *
     * @param mixed $sPKISexp
     * @return self
     */
    public function setSPKISexp(array $sPKISexp)
    {
        $this->sPKISexp = $sPKISexp;
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

