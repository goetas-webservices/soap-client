<?php

declare(strict_types=1);

namespace GoetasWebservices\SoapServices\SoapClient\Exception;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class UnexpectedFormatException extends ServerException
{
    public function __construct(ResponseInterface $response, RequestInterface $request, string $message, ?\Throwable $previous = null)
    {
        parent::__construct($response, $request, $previous);
        $this->message = $message;
    }
}
