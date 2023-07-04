<?php

declare(strict_types=1);

namespace GoetasWebservices\SoapServices\SoapClient\StubGeneration;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use GoetasWebservices\SoapServices\Metadata\Headers\Header;
use GoetasWebservices\SoapServices\SoapClient\StubGeneration\Tag\MethodTag;
use GoetasWebservices\SoapServices\SoapClient\StubGeneration\Tag\ParamTag;
use GoetasWebservices\XML\WSDLReader\Wsdl\Message\Part;
use GoetasWebservices\XML\WSDLReader\Wsdl\PortType;
use GoetasWebservices\Xsd\XsdToPhp\Naming\NamingStrategy;
use GoetasWebservices\Xsd\XsdToPhp\Php\PhpConverter;
use GoetasWebservices\Xsd\XsdToPhp\Php\Structure\PHPClass;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\DocBlockGenerator;

class ClientStubGenerator
{
    /**
     * @var NamingStrategy
     */
    private $namingStrategy;
    /**
     * @var PhpConverter
     */
    private $phpConverter;
    /**
     * @var bool
     */
    private $unwrapReturn = false;
    /**
     * @var Inflector
     */
    private $inflector;

    public function __construct(PhpConverter $phpConverter, NamingStrategy $namingStrategy, bool $unwrapReturn = false, array $baseNs = [])
    {
        $this->namingStrategy = $namingStrategy;
        $this->phpConverter = $phpConverter;
        $this->unwrapReturn = $unwrapReturn;
        $this->inflector = InflectorFactory::create()->build();
    }

    public function setUnwrap(bool $mode = true): void
    {
        $this->unwrapReturn = $mode;
    }

    /**
     * @param PortType[] $ports
     */
    public function generate(array $ports): array
    {
        $classes = [];
        foreach ($ports as $port) {
            $class = new ClassGenerator();
            if (false !== $this->visitPortType($class, $port)) {
                $classes[] = $class;
            }
        }

        return $classes;
    }

    private function visitPortType(ClassGenerator $class, PortType $portType): ?bool
    {
        if (!count($portType->getOperations())) {
            return false;
        }

        $docBlock = new DocBlockGenerator('Class representing ' . $portType->getName());
        $docBlock->setWordWrap(false);
        if ($portType->getDocumentation()) {
            $docBlock->setLongDescription($portType->getDocumentation());
        }

        $namespaces = $this->phpConverter->getNamespaces();
        $class->setNamespaceName($namespaces[$portType->getDefinition()->getTargetNamespace()] . '\\SoapStubs');
        $class->setName($this->inflector->classify($portType->getName()));
        $class->setDocblock($docBlock);

        foreach ($portType->getOperations() as $operation) {
            $operationTag = $this->visitOperation($operation);
            $docBlock->setTag($operationTag);
        }

        return null;
    }

    private function visitOperation(PortType\Operation $operation): MethodTag
    {
        $types = $this->getOperationReturnTypes($operation);
        $operationTag = new MethodTag(
            $this->inflector->camelize($operation->getName()),
            $types,
            preg_replace("/[\n\r]+/", ' ', $operation->getDocumentation())
        );
        $params = $this->getOperationParams($operation);
        $params[] = $param = new ParamTag('header', ['\\' . Header::class]);
        $param->setVaridic();

        $operationTag->setParams($params);

        return $operationTag;
    }

    private function getOperationParams(PortType\Operation $operation): array
    {
        if (!$operation->getInput()) {
            return [];
        }

        $parts = $operation->getInput()->getMessage()->getParts();
        if (!$parts) {
            return [];
        }

        if (count($parts) > 1) {
            $params = [];
            foreach ($parts as $part) {
                $partName = $this->namingStrategy->getPropertyName($part);
                $class = $this->getClassFromPart($part);

                $typeName = $class->getPhpType();
                if ($t = $class->isSimpleType()) {
                    $typeName = $t->getType()->getPhpType();
                }

                $params[] = $param = new ParamTag($partName, [$typeName]);
            }
        } else {
            $params = $this->extractSinglePartParameters(reset($parts));
        }

        return $params;
    }

    private function getOperationReturnTypes(PortType\Operation $operation): array
    {
        if (!$operation->getOutput() || !$operation->getOutput()->getMessage()->getParts()) {
            return ['void'];
        }

        $parts = $operation->getOutput()->getMessage()->getParts();
        if (count($parts) > 1) {
            return ['array'];
        }

        /**
         * @var $part \GoetasWebservices\XML\WSDLReader\Wsdl\Message\Part
         */
        $part = reset($parts);

        $class = $this->getClassFromPart($part);
        if ($this->unwrapReturn) {
            foreach ($class->getProperties() as $property) {
                $propertyClass = $property->getType();
                if ($t = $propertyClass->isSimpleType()) {
                    return [$t->getType()->getPhpType()];
                }

                return [$propertyClass->getPhpType()];
            }
        }

        if ($t = $class->isSimpleType()) {
            return [$t->getType()->getPhpType()];
        }

        return [$class->getPhpType()];
    }

    private function getClassFromPart(Part $part): PHPClass
    {
        if ($part->getType()) {
            return $this->phpConverter->visitType($part->getType());
        } else {
            return $this->phpConverter->visitElementDef($part->getElement());
        }
    }

    /**
     * @return array
     */
    private function extractSinglePartParameters(Part $part): array
    {
        $params = [];
        $class = $this->getClassFromPart($part);

        foreach ($class->getPropertiesInHierarchy() as $property) {
            $t = $property->getType()->getPhpType();
            $params[] = $param = new ParamTag($property->getName(), [$t]);
        }

        return $params;
    }
}
