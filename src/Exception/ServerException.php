<?php

declare(strict_types=1);

namespace GoetasWebservices\SoapServices\SoapClient\Exception;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ServerException extends SoapException
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ResponseInterface
     */
    private $response;

    public function __construct(string $message, ResponseInterface $response, RequestInterface $request, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->response = $response;
        $this->request = $request;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
