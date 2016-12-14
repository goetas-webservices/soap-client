<?php

namespace GoetasWebservices\SoapServices\SoapClient\SoapEnvelope\Messages;

/**
 * Class representing Fault
 */
class Fault
{

    /**
     * @property \GoetasWebservices\SoapServices\SoapClient\SoapEnvelope\Parts\Fault $fault
     */
    private $fault = null;

    /**
     * Gets as fault
     *
     * @return \GoetasWebservices\SoapServices\SoapClient\SoapEnvelope\Parts\Fault
     */
    public function getFault()
    {
        return $this->fault;
    }

    /**
     * Sets a new fault
     *
     * @param \GoetasWebservices\SoapServices\SoapClient\SoapEnvelope\Parts\Fault $fault
     * @return self
     */
    public function setFault(\GoetasWebservices\SoapServices\SoapClient\SoapEnvelope\Parts\Fault $fault)
    {
        $this->fault = $fault;
        return $this;
    }


}

