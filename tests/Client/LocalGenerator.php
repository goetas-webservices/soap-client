<?php


namespace GoetasWebservices\SoapServices\SoapClient\Tests\Client;


use GoetasWebservices\WsdlToPhp\Generation\JmsSoapConverter;
use GoetasWebservices\WsdlToPhp\Tests\Generator;
use GoetasWebservices\Xsd\XsdToPhp\Jms\YamlConverter;

class LocalGenerator extends Generator
{
    protected function generateJMSFiles(array $schemas, array $services)
    {
        $converter = new YamlConverter($this->namingStrategy);
        $soapConverter = new JmsSoapConverter($converter);

        $this->setNamespaces($converter);
        $items = $converter->convert($schemas);
        $items = array_merge($items, $soapConverter->visitServices($services));
        return $items;
    }
}