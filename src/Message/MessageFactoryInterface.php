<?php
namespace GoetasWebservices\SoapServices\SoapClient\Message;

use Psr\Http\Message\RequestInterface;

interface MessageFactoryInterface
{
    /**
     * @param string|UriInterface $uri
     * @param string|null|resource|StreamInterface $xml
     * @param string[] $headers
     * @return RequestInterface
     */
    public function getRequest($uri, $xml, array $headers = array());
}
