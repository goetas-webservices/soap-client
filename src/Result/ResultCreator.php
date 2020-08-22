<?php

namespace GoetasWebservices\SoapServices\SoapClient\Result;

use GoetasWebservices\SoapServices\SoapClient\SerializerUtils;
use JMS\Serializer\Serializer;
use Metadata\MetadataFactoryInterface;
use Metadata\PropertyMetadata;

class ResultCreator implements ResultCreatorInterface
{
    private $unwrap = false;
    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(Serializer $serializer, $unwrap = false)
    {
        $this->serializer = $serializer;
        $this->unwrap = $unwrap;
    }

    private function getPropertyValue($propertyMetadata, $object) {
        $reflectionProperty = new \ReflectionProperty($propertyMetadata->class, $propertyMetadata->name);
        $reflectionProperty->setAccessible(true);
        return $reflectionProperty->getValue($object);
    }

    public function prepareResult($object, array $output)
    {
        if (!count($output['parts'])) {
            return null;
        }
        $factory = SerializerUtils::getMetadataFactory($this->serializer);

        $classMetadata = $factory->getMetadataForClass($output['message_fqcn']);
        $bodyMetadata = $classMetadata->propertyMetadata['body'];
        $bodyClassMetadata = $factory->getMetadataForClass($bodyMetadata->type['name']);
        $body = $this->getPropertyValue($bodyMetadata, $object);
        $parts = [];
        foreach ($bodyClassMetadata->propertyMetadata as $propertyMetadata) {
            $parts[$propertyMetadata->name] = $this->getPropertyValue($propertyMetadata, $body);
        }
        if (count($output['parts']) > 1) {
            return $parts;
        } else {
            if ($this->unwrap) {
                foreach ($bodyClassMetadata->propertyMetadata as $propertyMetadata) {
                    $propClassMetadata = $factory->getMetadataForClass($propertyMetadata->type['name']);

                    if (count($propClassMetadata->propertyMetadata) > 1) {
                        throw new \Exception("When using wrapped mode, the wrapped object can not have multiple properties");
                    }
                    if (!count($propClassMetadata->propertyMetadata)) {
                        return null;
                    }
                    $propertyMetadata = reset($propClassMetadata->propertyMetadata);
                    return $this->getPropertyValue($propertyMetadata, reset($parts));
                }
            }
            return reset($parts);
        }
    }
}
