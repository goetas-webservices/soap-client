<?php

namespace GoetasWebservices\SoapServices\SoapClient\Envelope;

use GoetasWebservices\SoapServices\SoapClient\Exception\FaultException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface FaultMessageInterface
{
    /**
     * @param ResponseInterface $response
     * @param RequestInterface $request
     * @param \Exception $e
     * @return FaultException
     */
    public function createException(ResponseInterface $response, RequestInterface $request, \Exception $e = null);
}

