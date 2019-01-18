<?php

namespace GoetasWebservices\SoapServices\SoapClient\Exception;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class UnexpectedFormatException extends ServerException
{
    public function __construct(ResponseInterface $response, RequestInterface $request, $message)
    {
        parent::__construct($response, $request);
        $this->message = $message;
    }
}
