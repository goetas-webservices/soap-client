<?php
namespace GoetasWebservices\SoapServices\SoapClient\Command;

use GoetasWebservices\SoapServices\SoapClient\Builder\SoapContainerBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Code\Generator\FileGenerator;

class Generate extends Command
{
    protected function configure()
    {
        parent::configure();
        $this->setName('generate');
        $this->setDescription("Convert create all the necessary PHP classes for a SOAP client");
        $this->setDefinition([
            new InputArgument('config', InputArgument::REQUIRED, 'Config file location'),
            new InputArgument('dest-dir', InputArgument::REQUIRED, 'Config file location'),
            new InputOption('dest-class', null,  InputOption::VALUE_REQUIRED, 'Config file location'),
        ]);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $containerBuilder = new SoapContainerBuilder($input->getArgument('config'));

        if ($input->getOption('dest-class')) {
            $containerBuilder->setContainerClassName($input->getOption('dest-class'));
        }

        $debugContainer = $containerBuilder->getDebugContainer();

        $wsdlMetadata = $debugContainer->getParameter('wsdl2php.config')['metadata'];

        $schemas = [];
        $portTypes = [];
        $wsdlReader = $debugContainer->get('goetas.wsdl2php.wsdl_reader');

        foreach (array_keys($wsdlMetadata) as $src) {
            $definitions = $wsdlReader->readFile($src);
            $schemas[] = $definitions->getSchema();
            $portTypes = array_merge($portTypes, $definitions->getPortTypes());
        }

        $soapReader = $debugContainer->get('goetas.wsdl2php.soap_reader');


        foreach (['php', 'jms'] as $type) {
            $converter = $debugContainer->get('goetas.xsd2php.converter.' . $type);
            $wsdlConverter = $debugContainer->get('goetas.wsdl2php.converter.' . $type);
            $items = $wsdlConverter->visitServices($soapReader->getServices());
            $items = array_merge($items, $converter->convert($schemas));

            $writer = $debugContainer->get('goetas.xsd2php.writer.' . $type);
            $writer->write($items);
        }

        /**
         * @var $clientStubGenerator \GoetasWebservices\SoapServices\SoapClient\StubGeneration\ClientStubGenerator
         */
        $clientStubGenerator = $debugContainer->get('goetas.wsdl2php.stub.client_generator');

        $classDefinitions = $clientStubGenerator->generate($portTypes);
        $classWriter = $debugContainer->get('goetas.xsd2php.class_writer.php');
        $classWriter->write($classDefinitions);

        $containerDumped = $containerBuilder->dumpContainerForProd($input->getArgument('dest-dir'), $debugContainer);

        return $containerDumped ? 0 : 255;

    }
}
