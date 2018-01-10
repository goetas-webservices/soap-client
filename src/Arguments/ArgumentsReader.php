<?php

namespace GoetasWebservices\SoapServices\SoapClient\Arguments;

use Doctrine\Instantiator\Instantiator;
use GoetasWebservices\SoapServices\SoapClient\Arguments\Headers\Handler\HeaderHandler;
use GoetasWebservices\SoapServices\SoapClient\Arguments\Headers\Handler\HeaderPlaceholder;
use GoetasWebservices\SoapServices\SoapClient\Arguments\Headers\Header;
use JMS\Serializer\Serializer;

class ArgumentsReader implements ArgumentsReaderInterface
{
    /**
     * @var Serializer
     */
    private $serializer;
    /**
     * @var HeaderHandler
     */
    private $headerHandler;

    public function __construct(Serializer $serializer, HeaderHandler $headerHandler)
    {
        $this->serializer = $serializer;
        $this->headerHandler = $headerHandler;
    }

    /**
     * @param array $args
     * @param array $input
     * @return null|object
     */
    public function readArguments(array $args, array $input)
    {

        $envelope = array_filter($args, function ($item) use ($input) {
            return $item instanceof $input['message_fqcn'];
        });
        if ($envelope) {
            return reset($envelope);
        }

        $instantiator = new Instantiator();
        $envelope = $instantiator->instantiate($input['message_fqcn']);

        $args = $this->handleHeaders($args, $input, $envelope);

        if (!count($input['parts'])) {
            return $envelope;
        }

        $body = $instantiator->instantiate($input['part_fqcn']);
        $envelope->setBody($body);
        $factory = $this->serializer->getMetadataFactory();
        $classMetadata = $factory->getMetadataForClass($input['part_fqcn']);

        if (count($input['parts']) > 1) {

            if (count($input['parts']) !== count($args)) {
                throw new \Exception("Expected to have exactly " . count($input['parts']) . " arguments, supplied " . count($args));
            }

            foreach ($input['parts'] as $paramName => $elementName) {
                $propertyMetadata = $classMetadata->propertyMetadata[$paramName];
                $propertyMetadata->setValue($body, array_shift($args));
            }
            return $envelope;
        }

        $propertyName = key($input['parts']);
        $propertyMetadata = $classMetadata->propertyMetadata[$propertyName];
        if ($args[0] instanceof $propertyMetadata->type['name']) {
            $propertyMetadata->setValue($body, reset($args));
            return $envelope;
        }

        $instance2 = $instantiator->instantiate($propertyMetadata->type['name']);
        $classMetadata2 = $factory->getMetadataForClass($propertyMetadata->type['name']);
        $propertyMetadata->setValue($body, $instance2);

        foreach ($classMetadata2->propertyMetadata as $propertyMetadata2) {
            if (!count($args)) {
                throw new \Exception("Not enough arguments provided. Can't find a parameter to set " . $propertyMetadata2->name);
            }
            $value = array_shift($args);
            $propertyMetadata2->setValue($instance2, $value);
        }
        return $envelope;
    }

    /**
     * @param array $args
     * @param array $input
     * @param $envelope
     * @return array
     */
    private function handleHeaders(array $args, array $input, $envelope)
    {
        $headers = array_filter($args, function ($item) use ($input) {
            return $item instanceof $input['headers_fqcn'];
        });
        if ($headers) {
            $envelope->setHeader(reset($headers));
        } else {

            $headers = array_filter($args, function ($item) {
                return $item instanceof Header;
            });
            if (count($headers)) {
                $headerPlaceholder = new HeaderPlaceholder();
                foreach ($headers as $headerInfo) {
                    $this->headerHandler->addHeaderData($headerPlaceholder, $headerInfo);
                }
                $envelope->setHeader($headerPlaceholder);
            }
        }

        $args = array_filter($args, function ($item) use ($input) {
            return !($item instanceof Header) && !($item instanceof $input['headers_fqcn']);
        });
        return $args;
    }
}
