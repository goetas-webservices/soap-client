<?php
namespace GoetasWebservices\SoapServices\SoapClient\Arguments\Headers\Handler;

use GoetasWebservices\SoapServices\SoapClient\Arguments\Headers\Header;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Metadata\StaticPropertyMetadata;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\XmlDeserializationVisitor;
use JMS\Serializer\XmlSerializationVisitor;

class HeaderHandler implements SubscribingHandlerInterface
{
    protected $headerData = [];

    public static function getSubscribingMethods()
    {
        return array(
            array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'xml',
                'type' => HeaderPlaceholder::class,
                'method' => 'serializeHeader'
            ),
            array(
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format' => 'xml',
                'type' => 'GoetasWebservices\SoapServices\SoapEnvelope\Headers',
                'method' => 'deserializeHeaders'
            ),
        );
    }

    public function deserializeHeaders(XmlDeserializationVisitor $visitor, \SimpleXMLElement $data, array $type, DeserializationContext $context)
    {
        $newType = [
            'name' => $type['params'][0],
            'params' => []
        ];
        return $context->getNavigator()->accept($data, $newType, $context);
    }

    public function addHeaderData(HeaderPlaceholder $reference, $data)
    {
        $this->headerData[spl_object_hash($reference)][] = $data;
    }

    public function serializeHeader(XmlSerializationVisitor $visitor, HeaderPlaceholder $data, array $type, SerializationContext $context)
    {
        if (!isset($this->headerData[spl_object_hash($data)])) {
            return;
        }
        $factory = $context->getMetadataFactory();
        /**
         * @var $header Header
         */
        foreach ($this->headerData[spl_object_hash($data)] as $header) {
            /**
             * @var $classMetadata \JMS\Serializer\Metadata\ClassMetadata
             */
            $classMetadata = $factory->getMetadataForClass(get_class($header->getData()));

            $metadata = new StaticPropertyMetadata($classMetadata->name, $classMetadata->xmlRootName, $header->getData());
            $metadata->xmlNamespace = $classMetadata->xmlRootNamespace;
            $metadata->serializedName = $classMetadata->xmlRootName ?: 'header';

            $visitor->visitProperty($metadata, $header->getData(), $context);

            $this->handleOptions($visitor, $header);
        }
    }

    private function handleOptions(XmlSerializationVisitor $visitor, $header)
    {
        $options = $header->getOptions();
        if (!count($options)) {
            return;
        }
        /**
         * @var $currentNode \DOMNode
         */
        $currentNode = $visitor->getCurrentNode();
        foreach ($options as $option => $value) {
            if (in_array($option, ['mustUnderstand', 'required', 'role', 'actor'])) {

                $this->setAttributeOnNode($currentNode->lastChild, $option, $value, $currentNode->ownerDocument->documentElement->namespaceURI);
            }
        }
    }

    private function setAttributeOnNode(\DOMElement $node, $name, $value, $namespace)
    {
        if (!($prefix = $node->lookupPrefix($namespace)) && !($prefix = $node->ownerDocument->lookupPrefix($namespace))) {
            $prefix = 'ns-' . substr(sha1($namespace), 0, 8);
        }
        $node->setAttributeNS($namespace, $prefix . ':' . $name, is_bool($value) || is_null($value) ? ($value ? 'true' : 'false') : $value);
    }

}



