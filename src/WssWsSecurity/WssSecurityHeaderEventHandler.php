<?php
namespace GoetasWebservices\SoapServices\SoapClient\WssWsSecurity;

use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Secext\Security as SecextSecurity;
use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Secext\UsernameToken;
use GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Utility\Timestamp;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\StaticPropertyMetadata;

class WssSecurityHeaderEventHandler implements EventSubscriberInterface
{
    const WSS_UTP = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0';
    const DATETIME_FORMAT = 'Y-m-d\TH:i:s.000\Z';

    protected $headerData = [];

    public static function getSubscribedEvents()
    {
        return array(
            array(
                'event' => 'serializer.post_serialize',
                'method' => 'serializeAnyElement',
                'class' => SecextSecurity::class,
                'format' => 'xml'
            ),
            array(
                'event' => 'serializer.post_serialize',
                'method' => 'serializeAnyElement',
                'class' => UsernameToken::class,
                'format' => 'xml'
            ),
            array(
                'event' => 'serializer.post_serialize',
                'method' => 'serializeAnyElement',
                'class' => Timestamp::class,
                'format' => 'xml'
            ),
        );
    }

    public function serializeAnyElement(ObjectEvent $event)
    {
        /**
         * @var $security SecextSecurity
         */
        $security = $event->getObject();
        $items = $security->getAnyElement();
        if (!count($items)) {
            return;
        }
        $context = $event->getContext();
        $visitor = $event->getVisitor();
        $metadataFactory = $context->getMetadataFactory();;

        foreach ($items as $item) {
            /**
             * @var $itemMetadata ClassMetadata
             */
            $itemMetadata = $metadataFactory->getMetadataForClass(get_class($item));

            $prop = new StaticPropertyMetadata(get_class($item), $itemMetadata->xmlRootName, $item);
            $prop->serializedName = $itemMetadata->xmlRootName;
            $prop->xmlNamespace = $itemMetadata->xmlRootNamespace;

            $visitor->visitProperty($prop, $item, $context);
        }
    }
}
