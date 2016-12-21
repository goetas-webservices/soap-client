<?php
namespace GoetasWebservices\SoapServices\SoapClient\WssWsSecurity\Serializer;

use GoetasWebservices\SoapServices\SoapClient\Arguments\Headers\Handler\HeaderPlaceholder;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;

class WssSecurityHeaderEventListener implements EventSubscriberInterface
{
    /**
     * @var WsSecurityFilterResponse
     */
    protected $filter;

    public static function getSubscribedEvents()
    {
        return array(
            array(
                'event' => 'serializer.pre_deserialize',
                'method' => 'onPreDeserializeEvent',
                'class' => HeaderPlaceholder::class,
                'format' => 'xml'
            ),
            array(
                'event' => 'serializer.pre_deserialize',
                'method' => 'onPreDeserializeEvent',
                'class' => 'Ex\SoapEnvelope12\Messages\RequestHeaderInput',
                'format' => 'xml'
            ),
        );
    }

    public function __construct(WsSecurityFilterResponse $filter)
    {
        $this->filter = $filter;
    }

    public function onPreDeserializeEvent(PreDeserializeEvent $event)
    {
        $data = $event->getData();

        $envelope = dom_import_simplexml($data);
        $this->filter->filterDom($envelope->ownerDocument);

        $newData = simplexml_import_dom($envelope);
        $event->setData($newData);
    }
}
