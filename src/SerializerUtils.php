<?php

namespace GoetasWebservices\SoapServices\SoapClient;

use JMS\Serializer\Serializer;
use Metadata\MetadataFactory;

class SerializerUtils
{
    /**
     * Get metadata factory from serializer, with any JMS Serializer version.
     *
     * @param $serializer Serializer
     * @return MetadataFactory
     */
    public static function getMetadataFactory($serializer)
    {
        if (method_exists($serializer, "getMetadataFactory")) {
            // JMS Serializer 1.x
            return $serializer->getMetadataFactory();
        } else {
            // JMS Serializer 2.x & 3.x
            $reflectionProperty = new \ReflectionProperty(get_class($serializer), 'factory');
            $reflectionProperty->setAccessible(true);
            return $reflectionProperty->getValue($serializer);
        }
    }
}