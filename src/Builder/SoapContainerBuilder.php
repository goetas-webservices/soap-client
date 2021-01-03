<?php

declare(strict_types=1);

namespace GoetasWebservices\SoapServices\SoapClient\Builder;

use GoetasWebservices\SoapServices\Metadata\Builder\SoapContainerBuilder as BaseSoapContainerBuilder;
use GoetasWebservices\SoapServices\SoapClient\DependencyInjection\SoapClientExtension;

class SoapContainerBuilder extends BaseSoapContainerBuilder
{
    public function __construct(?string $configFile = null)
    {
        parent::__construct($configFile);
        $this->addExtension(new SoapClientExtension());
    }
}
