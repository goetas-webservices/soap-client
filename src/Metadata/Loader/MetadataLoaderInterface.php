<?php

namespace GoetasWebservices\SoapServices\SoapClient\Metadata\Loader;

interface MetadataLoaderInterface
{
    /**
     * @param $wsdl
     * @return array
     */
    public function load($wsdl);
}
