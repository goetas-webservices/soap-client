<?php
namespace GoetasWebservices\SoapServices\SoapClient\WssWsSecurity;

use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Secext\AttributedStringType;
use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Secext\Nonce;
use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Secext\Password;
use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Secext\Security as SecextSecurity;
use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Secext\UsernameToken;
use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Utility\Created;
use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Utility\Expires;
use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Utility\Timestamp;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Metadata\StaticPropertyMetadata;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\XmlSerializationVisitor;

class WssSecurityHeaderHandler implements SubscribingHandlerInterface
{
    /**
     * @var WsSecurityFilterRequest
     */
    protected $filter;

    public static function getSubscribingMethods()
    {
        return array(
            array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'xml',
                'type' => Security::class,
                'method' => 'serializeHeader'
            )
        );
    }

    public function __construct(WsSecurityFilterRequest $filterResponse)
    {
        $this->filter = $filterResponse;
    }

    public function serializeHeader(XmlSerializationVisitor $visitor, Security $data, array $type, SerializationContext $context)
    {

        $securityHeader = $this->filter->filterDom($visitor->getDocument(), $data);

        $currentNode = $visitor->getCurrentNode();
        $currentNode->parentNode->replaceChild($securityHeader, $currentNode);
        $visitor->revertCurrentNode();
        $visitor->setCurrentNode($securityHeader);
    }
}
