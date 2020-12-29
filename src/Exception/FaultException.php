<?php

declare(strict_types=1);

namespace GoetasWebservices\SoapServices\SoapClient\Exception;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class FaultException extends \Exception
{
    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(ResponseInterface $response, RequestInterface $request, ?\Throwable $previous = null)
    {
        parent::__construct('null', 0, $previous);
        $this->response = $response;
        $this->request = $request;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public static function createFromResponse(ResponseInterface $response, RequestInterface $request): FaultException
    {
        return new self($response, $request);
    }
}
