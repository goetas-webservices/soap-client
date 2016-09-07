<?php

namespace GoetasWebservices\SoapServices\SoapClient\Arguments;

interface ArgumentsReaderInterface
{
    /**
     * @param array $args
     * @param array $input
     * @return null|object
     */
    public function readArguments(array $args, array $input);
}
