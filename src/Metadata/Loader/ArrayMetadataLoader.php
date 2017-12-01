<?php

namespace GoetasWebservices\SoapServices\SoapClient\Metadata\Loader;

use GoetasWebservices\SoapServices\SoapClient\Exception\MetadataException;

class ArrayMetadataLoader implements MetadataLoaderInterface
{
    /**
     * @var array
     */
    private $metadata = [];

    public function __construct(array $metadata)
    {
        $this->metadata = $metadata;
    }

    public function addMetadata($wsdl, array $metadata)
    {
        $this->metadata[$wsdl] = $metadata;
    }

    public function load($wsdl)
    {
        if (!isset($this->metadata[$wsdl])) {
            throw new MetadataException(sprintf("Can not load metadata information for %s", $wsdl));
        }
        return $this->metadata[$wsdl];
    }
}
