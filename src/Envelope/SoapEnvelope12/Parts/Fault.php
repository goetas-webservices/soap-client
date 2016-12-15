<?php

namespace GoetasWebservices\SoapServices\SoapClient\Envelope\SoapEnvelope12\Parts;

/**
 * Class representing DoSomethingInput
 */
class Fault
{
    /**
     * @var FaultCode
     */
    private $code;
    /**
     * @var string[]
     */
    private $reason = [];
    /**
     * @var string
     */
    private $detail;
    /**
     * @var string
     */
    private $role;
    /**
     * @var string
     */
    private $node;

    /**
     * @return string
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * @param string $detail
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @return string
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $role
     * @return Fault
     */
    public function setRole($role)
    {
        $this->role = $role;
    }

    /**
     * @param FaultCode $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @param string[] $reason
     */
    public function setReason(array $reason)
    {
        $this->reason = $reason;
    }

    /**
     * @return string[]
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param string $node
     */
    public function setNode($node)
    {
        $this->node = $node;
    }

}

