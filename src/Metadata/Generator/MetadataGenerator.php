<?php

namespace GoetasWebservices\SoapServices\SoapClient\Metadata\Generator;

use Doctrine\Common\Inflector\Inflector;
use GoetasWebservices\XML\SOAPReader\Soap\Operation;
use GoetasWebservices\XML\SOAPReader\Soap\OperationMessage;
use GoetasWebservices\XML\SOAPReader\Soap\Service;
use GoetasWebservices\XML\WSDLReader\Wsdl\Message\Part;
use GoetasWebservices\XML\WSDLReader\Wsdl\PortType\Param;
use GoetasWebservices\Xsd\XsdToPhp\Naming\NamingStrategy;

class MetadataGenerator implements MetadataGeneratorInterface
{
    protected $namespaces = [];

    protected $alternativeEndpoints = [];

    protected $baseNs = [
        '1.1' => [
            'headers' => '\\SoapEnvelope\\Headers',
            'parts' => '\\SoapParts',
            'messages' => '\\SoapEnvelope\\Messages',
        ],
        '1.2' => [
            'headers' => '\\SoapEnvelope12\\Headers',
            'parts' => '\\SoapParts',
            'messages' => '\\SoapEnvelope12\\Messages',
        ]
    ];
    /**
     * @var NamingStrategy
     */
    private $namingStrategy;
    /**
     * @var bool
     */
    private $unwrap = false;

    /**
     * @param NamingStrategy $namingStrategy
     * @param array $namespaces
     */
    public function __construct(NamingStrategy $namingStrategy, array $namespaces)
    {
        $this->namespaces = $namespaces;
        $this->namingStrategy = $namingStrategy;
    }

    public function addAlternativeEndpoint($service, $port, $endPoint)
    {
        if (0 === strpos($endPoint, 'env(') && ')' === substr($endPoint, -1) && 'env()' !== $endPoint) {
            $endPoint = "%$endPoint%";
        }
        $this->alternativeEndpoints[$service][$port] = $endPoint;
    }

    public function setUnwrap($mode = true)
    {
        $this->unwrap = (bool)$mode;
    }


    /**
     * @param Service[] $soapServices
     * @return array
     */
    public function generate(array $soapServices)
    {
        $services = [];
        foreach ($soapServices as $soapService) {
            $port = $soapService->getPort();

            $endpoint = isset($this->alternativeEndpoints[$port->getService()->getName()][$port->getName()]) ? $this->alternativeEndpoints[$port->getService()->getName()][$port->getName()] : $soapService->getAddress();

            $services[$port->getService()->getName()][$port->getName()] = [
                'operations' => $this->generateService($soapService),
                'unwrap' => $this->unwrap,
                'endpoint' => $endpoint,
            ];

        }
        return $services;
    }

    protected function generateService(Service $service)
    {
        $operations = [];

        foreach ($service->getOperations() as $operation) {
            $operations[$operation->getOperation()->getName()] = $this->generateOperation($operation, $service);
        }
        return $operations;
    }

    protected function generateOperation(Operation $soapOperation, Service $service)
    {

        $operation = [
            'action' => $soapOperation->getAction(),
            'style' => $soapOperation->getStyle(),
            'version' => $service->getVersion(),
            'name' => $soapOperation->getOperation()->getName(),
            'method' => Inflector::camelize($soapOperation->getOperation()->getName()),
            'input' => $this->generateInOut($soapOperation, $soapOperation->getInput(), $soapOperation->getOperation()->getPortTypeOperation()->getInput(), 'Input', $service),
            'output' => $this->generateInOut($soapOperation, $soapOperation->getOutput(), $soapOperation->getOperation()->getPortTypeOperation()->getOutput(), 'Output', $service),
            'fault' => []
        ];

        /**
         * @var $fault \GoetasWebservices\XML\SOAPReader\Soap\Fault
         */

        foreach ($soapOperation->getFaults() as $fault) {
            //$operation['fault'][$fault->getName()] = $fault->get;
            // @todo do faults metadata
        }

        return $operation;
    }

    protected function generateInOut(Operation $operation, OperationMessage $operationMessage, Param $param, $direction, Service $service)
    {
        $xmlNs = $operation->getOperation()->getDefinition()->getTargetNamespace();
        if (!isset($this->namespaces[$xmlNs])) {
            throw new \Exception("Can not find a PHP namespace to be associated with '$xmlNs' XML namespace");
        }
        $ns = $this->namespaces[$xmlNs];
        $operation = [
            'message_fqcn' => $ns
                . $this->baseNs[$service->getVersion()]['messages'] . '\\'
                . Inflector::classify($operationMessage->getMessage()->getOperation()->getName())
                . $direction,
            'headers_fqcn' => $ns
                . $this->baseNs[$service->getVersion()]['headers'] . '\\'
                . Inflector::classify($operationMessage->getMessage()->getOperation()->getName())
                . $direction,
            'part_fqcn' => $ns
                . $this->baseNs[$service->getVersion()]['parts'] . '\\'
                . Inflector::classify($operationMessage->getMessage()->getOperation()->getName())
                . $direction,
            'parts' => $this->getParts($param->getMessage()->getParts())
        ];

        return $operation;
    }

    /**
     * @param Part[] $messageParts
     * @return array
     */
    private function getParts(array $messageParts)
    {
        $parts = [];
        foreach ($messageParts as $partName => $part) {
            $partName = $this->namingStrategy->getPropertyName($part);
            if ($part->getType()) {
                $parts [$partName] = $this->namingStrategy->getTypeName($part->getType());
            } else {
                $parts [$partName] = $this->namingStrategy->getItemName($part->getElement());
            }
        }
        return $parts;
    }
}

