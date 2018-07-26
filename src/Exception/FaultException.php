<?php

namespace GoetasWebservices\SoapServices\SoapClient\Exception;

class FaultException extends ServerException
{

    public function getFault()
    {
        throw new \LogicException('This method must be override');
    }
}
