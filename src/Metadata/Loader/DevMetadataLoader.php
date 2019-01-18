<?php

namespace GoetasWebservices\SoapServices\SoapClient\Metadata\Loader;

use GoetasWebservices\SoapServices\SoapClient\Exception\MetadataException;
use GoetasWebservices\SoapServices\SoapClient\Metadata\Generator\MetadataGenerator;
use GoetasWebservices\XML\SOAPReader\SoapReader;
use GoetasWebservices\XML\WSDLReader\DefinitionsReader;

/**
 * This class is here to be used only while developing, should not be used on production.
 */
class DevMetadataLoader implements MetadataLoaderInterface
{
    /**
     * @var array
     */
    private $metadataCache = [];
    /**
     * @var MetadataGenerator
     */
    private $metadataGenerator;
    /**
     * @var DefinitionsReader
     */
    private $wsdlReader;
    /**
     * @var SoapReader
     */
    private $soapReader;

    public function __construct(MetadataGenerator $metadataGenerator, SoapReader $soapReader, DefinitionsReader $wsdlReader)
    {
        $this->metadataGenerator = $metadataGenerator;
        $this->wsdlReader = $wsdlReader;
        $this->soapReader = $soapReader;
    }

    public function load($wsdl)
    {
        if (!isset($this->metadataCache[$wsdl])) {
            $this->wsdlReader->readFile($wsdl);
            try {
                $this->metadataCache[$wsdl] = $this->metadataGenerator->generate($this->soapReader->getServices());
            } catch (\Exception $e) {
                throw new MetadataException(sprintf("Can not generate metadata information for %s", $wsdl), 0, $e);
            }
        }

        return $this->metadataCache[$wsdl];
    }
}
