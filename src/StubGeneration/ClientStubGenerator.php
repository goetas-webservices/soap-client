<?php
namespace GoetasWebservices\SoapServices\SoapClient\StubGeneration;

use Doctrine\Common\Inflector\Inflector;
use GoetasWebservices\SoapServices\SoapClient\StubGeneration\Tag\MethodTag;
use GoetasWebservices\SoapServices\SoapClient\StubGeneration\Tag\ParamTag;
use GoetasWebservices\XML\WSDLReader\Wsdl\Message\Part;
use GoetasWebservices\XML\WSDLReader\Wsdl\PortType;
use GoetasWebservices\Xsd\XsdToPhp\Naming\NamingStrategy;
use GoetasWebservices\Xsd\XsdToPhp\Php\PhpConverter;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\DocBlockGenerator;

class ClientStubGenerator
{
    protected $reservedWords = [
        'int',
        'float',
        'bool',
        'string',
        'true',
        'false',
        'null',
        'resource',
        'object',
        'mixed',
        'numeric',
    ];

    private $baseNs = [
        'headers' => '\\SoapEnvelope\\Headers',
        'parts' => '\\SoapEnvelope\\Parts',
        'messages' => '\\SoapEnvelope\\Messages',
    ];
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

    public function __construct(PhpConverter $phpConverter, NamingStrategy $namingStrategy, $unwrapReturn = false, array $baseNs = array())
    {
        foreach ($baseNs as $k => $ns) {
            if (isset($this->baseNs[$k])) {
                $this->baseNs[$k] = $ns;
            }
        }
        $this->baseNs = $baseNs;
        $this->namingStrategy = $namingStrategy;
        $this->phpConverter = $phpConverter;
        $this->unwrapReturn = $unwrapReturn;
    }

    public function setUnwrap($mode = true)
    {
        $this->unwrapReturn = (bool)$mode;
    }

    /**
     * @param PortType[] $ports
     * @return ClassGenerator
     */
    public function generate(array $ports)
    {
        $classes = array();
        foreach ($ports as $port) {
            $class = new ClassGenerator();
            if ($this->visitPortType($class, $port) !== false) {
                $classes[] = $class;
            }
        }
        return $classes;
    }

    private function visitPortType(ClassGenerator $class, PortType $portType)
    {
        if (!count($portType->getOperations())) {
            return false;
        }
        $docBlock = new DocBlockGenerator("Class representing " . $portType->getName());
        $docBlock->setWordWrap(false);
        if ($portType->getDocumentation()) {
            $docBlock->setLongDescription($portType->getDocumentation());
        }

        $namespaces = $this->phpConverter->getNamespaces();
        $class->setNamespaceName($namespaces[$portType->getDefinition()->getTargetNamespace()] . "\\SoapStubs");
        $class->setName(Inflector::classify($portType->getName()));
        $class->setDocblock($docBlock);

        foreach ($portType->getOperations() as $operation) {
            $operationTag = $this->visitOperation($operation);
            $docBlock->setTag($operationTag);
        }
    }

    private function visitOperation(PortType\Operation $operation)
    {
        $types = $this->getOperationReturnTypes($operation);
        $operationTag = new MethodTag(
            Inflector::camelize($operation->getName()),
            $types,
            preg_replace("/[\n\r]+/", " ", $operation->getDocumentation())
        );
        $params = $this->getOperationParams($operation);
        $operationTag->setParams($params);
        return $operationTag;
    }

    private function getOperationParams(PortType\Operation $operation)
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

    private function getOperationReturnTypes(PortType\Operation $operation)
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

    private function getClassFromPart(Part $part)
    {
        if ($part->getType()) {
            return $this->phpConverter->visitType($part->getType());
        } else {
            return $this->phpConverter->visitElementDef($part->getElement());
        }
    }

    /**
     * @param $part
     * @return array
     */
    private function extractSinglePartParameters(Part $part)
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
