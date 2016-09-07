<?php
namespace GoetasWebservices\SoapServices\SoapClient\Arguments;

use Doctrine\Instantiator\Instantiator;
use JMS\Serializer\Serializer;

class ArgumentsReader implements ArgumentsReaderInterface
{
    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param array $args
     * @param array $input
     * @return null|object
     */
    public function readArguments(array $args, array $input)
    {
        if (!count($input['parts'])) {
            return null;
        }

        $instantiator = new Instantiator();
        $factory = $this->serializer->getMetadataFactory();
        $instance = $instantiator->instantiate($input['part_fqcn']);
        $classMetadata = $factory->getMetadataForClass($input['part_fqcn']);

        if (count($input['parts']) > 1) {
            foreach ($input['parts'] as $paramName) {
                $propertyMetadata = $classMetadata->propertyMetadata[$paramName];
                $propertyMetadata->setValue($instance, $args[$paramName]);
            }
            return $instance;
        }

        $propertyMetadata = $classMetadata->propertyMetadata[reset($input['parts'])];
        if (count($args) === 1 && $args[0] instanceof $propertyMetadata->type['name']) {
            $propertyMetadata->setValue($instance, reset($args));
            return $instance;
        }

        $instance2 = $instantiator->instantiate($propertyMetadata->type['name']);
        $classMetadata2 = $factory->getMetadataForClass($propertyMetadata->type['name']);
        $propertyMetadata->setValue($instance, $instance2);

        foreach ($classMetadata2->propertyMetadata as $propertyMetadata2) {
            if (!count($args)) {
                throw new \Exception("Not enough arguments provided. Can't fina a parameter to set " . $propertyMetadata2->name);
            }
            $value = array_pop($args);
            $propertyMetadata2->setValue($instance2, $value);
        }
        return $instance;
    }
}
