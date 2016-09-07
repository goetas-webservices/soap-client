<?php

namespace GoetasWebservices\SoapServices\SoapClient\Exception;

use GoetasWebservices\SoapServices\SoapCommon\SoapEnvelope\Parts\Fault;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class FaultException extends ServerException
{
    /**
     * @var Fault
     */
    private $fault;

    public function __construct(Fault $fault, ResponseInterface $response, RequestInterface $request, $message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct($response, $request, $message, $code, $previous);
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
