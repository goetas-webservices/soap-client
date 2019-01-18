<?php

namespace GoetasWebservices\SoapServices\SoapClient\Arguments\Headers;

class MustUnderstandHeader extends Header
{
    public function __construct($data, array $options = [])
    {
        parent::__construct($data, array_merge(['mustUnderstand' => true], $options));
    }
}
