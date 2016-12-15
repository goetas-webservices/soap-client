<?php

namespace GoetasWebservices\SoapServices\SoapClient\Exception;

use GoetasWebservices\SoapServices\SoapClient\Envelope\SoapEnvelope12\Parts\Fault;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Fault12Exception extends FaultException
{
    /**
     * @var Fault
     */
    private $fault;

    public function __construct(Fault $fault, ResponseInterface $response, RequestInterface $request, \Exception $previous = null)
    {
        parent::__construct($response, $request, $previous);
        $this->fault = $fault;
    }

    /**
     * @return Fault
     */
    public function getFault()
    {
        return $this->fault;
    }
}
