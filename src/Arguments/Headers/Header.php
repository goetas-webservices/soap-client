<?php

namespace GoetasWebservices\SoapServices\SoapClient\Arguments\Headers;

class Header
{
    private $data;
    /**
     * @var array
     */
    private $options = [];

    public function __construct($data, array $options = [])
    {
        $this->data = $data;
        $this->options = $options;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getOptions()
    {
        return $this->options;
    }
}
