<?php

declare(strict_types=1);

namespace GoetasWebservices\SoapServices\SoapClient\Exception;

use GoetasWebservices\SoapServices\Metadata\Envelope\SoapEnvelope\Messages\Fault;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Fault11Exception extends FaultException
{
    /**
     * @var Fault
     */
    private $fault;

    private function __construct(Fault $fault, ResponseInterface $response, RequestInterface $request, ?\Throwable $previous = null)
    {
        $message = $fault->getBody()->getFault()->getString();

        parent::__construct($message, $response, $request, $previous);
        $this->fault = $fault;
    }

    public function getFault(): Fault
    {
        return $this->fault;
    }

    public static function createFromResponse(ResponseInterface $response, RequestInterface $request, Fault $fault): FaultException
    {
        return new self($fault, $response, $request);
    }
}
