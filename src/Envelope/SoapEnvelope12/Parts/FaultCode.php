<?php

namespace GoetasWebservices\SoapServices\SoapClient\Envelope\SoapEnvelope12\Parts;

class FaultCode
{
    /**
     * @var string
     */
    private $value;

    /**
     * @var FaultCode
     */
    private $subcode;

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return FaultCode
     */
    public function getSubcode()
    {
        return $this->subcode;
    }

    /**
     * @param FaultCode $subcode
     */
    public function setSubcode(FaultCode $subcode)
    {
        $this->subcode = $subcode;
    }

    public function __toString()
    {
        return $this->value . ($this->subcode ? (":" . $this->subcode) : "");
    }
}

