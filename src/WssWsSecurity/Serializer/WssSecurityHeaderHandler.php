<?php
namespace GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Serializer;


use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Security;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
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
            ),
        );
    }

    public function __construct(WsSecurityFilterRequest $filter)
    {
        $this->filter = $filter;
    }

    public function serializeHeader(XmlSerializationVisitor $visitor, Security $data, array $type, SerializationContext $context)
    {
        $currentNode = $visitor->getCurrentNode();
        $securityHeader = $this->filter->filterDom($currentNode, $data);
        $visitor->revertCurrentNode();
        $visitor->setCurrentNode($securityHeader);
    }
}
