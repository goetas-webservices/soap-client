<?php

namespace GoetasWebservices\SoapServices\SoapClient\Exception;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ServerException extends \Exception
{
    private $request;
    private $response;

    public function __construct(ResponseInterface $response, RequestInterface $request, \Exception $previous = null)
    {
        parent::__construct("Server error", null, $previous);
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
