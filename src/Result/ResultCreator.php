<?php

declare(strict_types=1);

namespace GoetasWebservices\SoapServices\SoapClient\Result;

use GoetasWebservices\SoapServices\Metadata\SerializerUtils;
use JMS\Serializer\Serializer;
use Metadata\PropertyMetadata;

class ResultCreator implements ResultCreatorInterface
{
    /**
     * @var bool
     */
    private $unwrap = false;
    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(Serializer $serializer, bool $unwrap = false)
    {
        $this->serializer = $serializer;
        $this->unwrap = $unwrap;
    }

    /**
     * @return mixed
     */
    private function getPropertyValue(PropertyMetadata $propertyMetadata, object $object)
    {
        $reflectionProperty = new \ReflectionProperty($propertyMetadata->class, $propertyMetadata->name);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }

    /**
     * @return mixed
     */
    public function prepareResult(object $object, array $output)
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
                        throw new \Exception('When using wrapped mode, the wrapped object can not have multiple properties');
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
