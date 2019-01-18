<?php

namespace GoetasWebservices\SoapServices\SoapClient\Result;

interface ResultCreatorInterface
{
    public function prepareResult($object, array $output);
}
