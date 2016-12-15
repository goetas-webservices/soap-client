<?php

namespace GoetasWebservices\SoapServices\SoapClient\Envelope\SoapEnvelope12\Messages;

use GoetasWebservices\SoapServices\SoapClient\Envelope\FaultMessageInterface;
use GoetasWebservices\SoapServices\SoapClient\Exception\Fault12Exception;
use GoetasWebservices\SoapServices\SoapClient\Exception\FaultException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class representing Body
 */
class Fault implements FaultMessageInterface
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

    /**
     * @param ResponseInterface $response
     * @param RequestInterface $request
     * @param \Exception $e
     * @return FaultException
     */
    public function createException(ResponseInterface $response, RequestInterface $request, \Exception $e = null)
    {
        if (!$this->getBody() || !$this->getBody()->getFault()) {
            throw new FaultException($response, $request, $e);
        }
        return new Fault12Exception($this->getBody()->getFault(), $response, $request, $e);
    }

}

