<?php

declare(strict_types=1);

namespace GoetasWebservices\SoapServices\SoapClient\Command;

use GoetasWebservices\SoapServices\SoapClient\Builder\SoapContainerBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class Generate extends Command
{
    protected function configure(): void
    {
        parent::configure();
        $this->setName('generate');
        $this->setDescription('Create all the necessary PHP classes for a SOAP client');
        $this->setDefinition([
            new InputArgument('config', InputArgument::REQUIRED, 'Config file location'),
            new InputArgument('dest-dir', InputArgument::REQUIRED, 'Container files destination directory'),
            new InputOption('dest-class', null, InputOption::VALUE_REQUIRED, 'Container class file destination directory'),
        ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logger = new ConsoleLogger($output);
        $containerBuilder = new SoapContainerBuilder($input->getArgument('config'), $logger);

        if ($input->getOption('dest-class')) {
            $containerBuilder->setContainerClassName($input->getOption('dest-class'));
        }

        $debugContainer = $containerBuilder->getDebugContainer();
        //$debugContainer->set('logger', $logger);

        $wsdlMetadata = $debugContainer->getParameter('goetas_webservices.soap.config')['metadata'];

        $schemas = [];
        $portTypes = [];
        $wsdlReader = $debugContainer->get('goetas_webservices.wsdl2php.wsdl_reader');

        foreach (array_keys($wsdlMetadata) as $src) {
            $definitions = $wsdlReader->readFile($src);
            $schemas[] = $definitions->getSchema();
            $portTypes = array_merge($portTypes, $definitions->getAllPortTypes());
        }

        $soapReader = $debugContainer->get('goetas_webservices.wsdl2php.soap_reader');
        $soapServices = $soapReader->getServices();

        foreach (['php', 'jms'] as $type) {
            $converter = $debugContainer->get('goetas_webservices.xsd2php.converter.' . $type);
            $wsdlConverter = $debugContainer->get('goetas_webservices.wsdl2php.converter.' . $type);
            $items = $wsdlConverter->visitServices($soapServices);
            $items = array_merge($items, $converter->convert($schemas));

            $writer = $debugContainer->get('goetas_webservices.xsd2php.writer.' . $type);
            $writer->write($items);
        }

        $containerBuilder->dumpContainerForProd($input->getArgument('dest-dir'), $debugContainer);

        $stubGenerator = $debugContainer->get('goetas_webservices.soap.stub.client_generator');

        $classDefinitions = $stubGenerator->generate($portTypes);
        $classWriter = $debugContainer->get('goetas_webservices.xsd2php.class_writer.php');
        $classWriter->write($classDefinitions);

        return 0;
    }
}
