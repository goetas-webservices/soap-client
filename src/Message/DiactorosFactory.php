<?php
namespace GoetasWebservices\SoapServices\SoapClient\Message;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Zend\Diactoros\Request;

class DiactorosFactory implements MessageFactoryInterface
{
    /**
     * @param string|UriInterface $uri
     * @param string|null|resource|StreamInterface $xml
     * @param string[] $headers
     * @return RequestInterface
     */
    public function getRequest($uri, $xml, array $headers = array())
    {
        return new Request($uri, 'POST', self::toStream($xml), $headers);
    }

    /**
     * @param string $str
     * @return Stream
     */
    public static function toStream($str)
    {
        $body = new Stream('php://temp', 'w');
        $body->write($str);
        $body->rewind();
        return $body;
    }
}
