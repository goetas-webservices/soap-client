<?php

namespace GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\XmlSign;

/**
 * Class representing RSAKeyValueType
 *
 *
 * XSD Type: RSAKeyValueType
 */
class RSAKeyValueType
{

    /**
     * @property mixed $modulus
     */
    private $modulus = null;

    /**
     * @property mixed $exponent
     */
    private $exponent = null;

    /**
     * Gets as modulus
     *
     * @return mixed
     */
    public function getModulus()
    {
        return $this->modulus;
    }

    /**
     * Sets a new modulus
     *
     * @param mixed $modulus
     * @return self
     */
    public function setModulus($modulus)
    {
        $this->modulus = $modulus;
        return $this;
    }

    /**
     * Gets as exponent
     *
     * @return mixed
     */
    public function getExponent()
    {
        return $this->exponent;
    }

    /**
     * Sets a new exponent
     *
     * @param mixed $exponent
     * @return self
     */
    public function setExponent($exponent)
    {
        $this->exponent = $exponent;
        return $this;
    }


}

