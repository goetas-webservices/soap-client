<?php
namespace GoetasWebservices\SoapServices\SoapClient\Message;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class GuzzleFactory implements MessageFactoryInterface
{
    /**
     * @param string|UriInterface $uri
     * @param string|null|resource|StreamInterface $xml
     * @param string[] $headers
     * @return RequestInterface
     */
    public function getRequest($uri, $xml, array $headers = array())
    {
        return new Request('POST', $uri, $headers, $xml);
    }
}
