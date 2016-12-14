<?php

namespace GoetasWebservices\SoapServices\SoapClient\SoapEnvelope\Parts;

/**
 * Class representing DoSomethingInput
 */
class Fault
{
    /**
     * @var string
     */
    private $actor;
    /**
     * @var \Exception
     */
    private $exception;

    public function setException(\Exception $exception)
    {
        $this->exception = $exception;
    }

    /**
     * @return string
     */
    public function getActor()
    {
        return $this->actor;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        $ref = new \ReflectionObject($this->exception);
        return 'SOAP-ENV:' . str_replace('Exception', '', $ref->getShortName()); //$this->exception->getCode();
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->exception->getMessage();
    }

    /**
     * @param string $actor
     * @return Fault
     */
    public function setActor($actor)
    {
        $this->actor = $actor;
    }

}

