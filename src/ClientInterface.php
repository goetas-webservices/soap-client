<?php

namespace GoetasWebservices\SoapServices\SoapClient;


use Psr\Http\Message\ResponseInterface;

interface ClientInterface
{
    public function __call($functionName, array $args);
    public function findFaultClass(ResponseInterface $response);
}