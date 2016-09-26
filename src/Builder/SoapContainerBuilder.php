<?php
namespace GoetasWebservices\SoapServices\SoapClient\Builder;

use GoetasWebservices\SoapServices\SoapClient\DependencyInjection\Compiler\CleanupPass;
use GoetasWebservices\SoapServices\SoapClient\DependencyInjection\Compiler\ConfigurePass;
use GoetasWebservices\SoapServices\SoapClient\DependencyInjection\SoapClientExtension;
use GoetasWebservices\SoapServices\SoapCommon\Builder\SoapContainerBuilder as BaseSoapContainerBuilder;

class SoapContainerBuilder extends BaseSoapContainerBuilder
{

    public function __construct($configFile = null)
    {
        parent::__construct();
        $this->setConfigFile($configFile);
        $this->addExtension(new SoapClientExtension());
        $this->addCompilerPass(new ConfigurePass());
        $this->addCompilerPass(new CleanupPass());
    }
}
