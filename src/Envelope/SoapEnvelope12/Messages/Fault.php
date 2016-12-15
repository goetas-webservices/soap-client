<?php

namespace GoetasWebservices\SoapServices\SoapClient\Envelope\SoapEnvelope12\Messages;

/**
 * Class representing Body
 */
class Fault
{

    /**
     * @property \GoetasWebservices\SoapServices\SoapClient\Envelope\SoapEnvelope12\Messages\FaultBody $body
     */
    private $body = null;

    /**
     * Gets as body
     *
     * @return \GoetasWebservices\SoapServices\SoapClient\Envelope\SoapEnvelope12\Messages\FaultBody
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Sets a new body
     *
     * @param \GoetasWebservices\SoapServices\SoapClient\Envelope\SoapEnvelope12\Messages\FaultBody $body
     * @return self
     */
    public function setBody(\GoetasWebservices\SoapServices\SoapClient\Envelope\SoapEnvelope12\Messages\FaultBody $body)
    {
        $this->body = $body;
        return $this;
    }


}

