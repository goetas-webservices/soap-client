<?php

declare(strict_types=1);

namespace GoetasWebservices\SoapServices\SoapClient\Result;

interface ResultCreatorInterface
{
    /**
     * @return mixed
     */
    public function prepareResult(object $object, array $output);
}
