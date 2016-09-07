<?php

namespace GoetasWebservices\SoapServices\SoapClient\Exception;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ServerException extends \Exception
{
    private $request;
    private $response;

    public function __construct(ResponseInterface $response, RequestInterface $request, $message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
        $this->request = $request;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }
}
