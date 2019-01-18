<?php

namespace GoetasWebservices\SoapServices\SoapClient\Envelope\Handler;

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\XmlDeserializationVisitor;

class FaultHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        return array(
            array(
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format' => 'xml',
                'type' => 'GoetasWebservices\SoapServices\SoapClient\Envelope\SoapEnvelope12\Parts\Fault\FaultDetail',
                'method' => 'deserializeFaultDetail'
            ),
        );
    }

    public function deserializeFaultDetail(XmlDeserializationVisitor $visitor, \SimpleXMLElement $data, array $type, DeserializationContext $context)
    {
        $domElement = dom_import_simplexml($data);

        $document = new \DOMDocument('1.0', 'utf-8');

        $xml = '';
        foreach ($domElement->childNodes as $child) {
            $newEl = $document->importNode($child, true);
            $xml .= $document->saveXML($newEl);
        }
        return $xml;
    }
}



