<?php

declare(strict_types=1);

namespace GoetasWebservices\SoapServices\SoapClient\Builder;

use GoetasWebservices\SoapServices\Metadata\Arguments\Headers\Handler\FaultHandler;
use GoetasWebservices\SoapServices\Metadata\Arguments\Headers\Handler\HeaderHandler;
use GoetasWebservices\SoapServices\SoapClient\DependencyInjection\Compiler\CleanupPass;
use GoetasWebservices\SoapServices\SoapClient\DependencyInjection\SoapClientExtension;
use GoetasWebservices\Xsd\XsdToPhpRuntime\Jms\Handler\BaseTypesHandler;
use GoetasWebservices\Xsd\XsdToPhpRuntime\Jms\Handler\XmlSchemaDateHandler;
use JMS\Serializer\Handler\HandlerRegistryInterface;
use JMS\Serializer\SerializerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class SoapContainerBuilder
{
    /**
     * @var string
     */
    private $className = 'SoapClientContainer';
    /**
     * @var string
     */
    private $classNs = 'SoapServicesStub';
    /**
     * @var string
     */
    protected $configFile = 'config.yml';
    /**
     * @var string[]
     */
    protected $extensions = [];
    /**
     * @var string[]
     */
    protected $compilerPasses = [];

    /**
     * @var string
     */
    protected static $metadataForSoapEnvelope = [
        'GoetasWebservices\SoapServices\Metadata\Envelope\SoapEnvelope12' => __DIR__ . '/../Resources/metadata/jms12',
        'GoetasWebservices\SoapServices\Metadata\Envelope\SoapEnvelope' => __DIR__ . '/../Resources/metadata/jms',
    ];

    public function __construct(?string $configFile = null)
    {
        $this->setConfigFile($configFile);
        $this->addExtension(new SoapClientExtension());
        $this->addCompilerPass(new CleanupPass());
    }

    public function setConfigFile(string $configFile): void
    {
        $this->configFile = $configFile;
    }

    protected function addExtension(ExtensionInterface $extension): void
    {
        $this->extensions[] = $extension;
    }

    protected function addCompilerPass(CompilerPassInterface $pass): void
    {
        $this->compilerPasses[] = $pass;
    }

    public function setContainerClassName(string $fqcn): void
    {
        $fqcn = strtr($fqcn, [
            '.' => '\\',
            '/' => '\\',
        ]);
        $pos = strrpos($fqcn, '\\');
        $this->className = substr($fqcn, $pos + 1);
        $this->classNs = substr($fqcn, 0, $pos);
    }

    /**
     * @param array $metadata
     */
    protected function getContainerBuilder(array $metadata = []): ContainerBuilder
    {
        $container = new ContainerBuilder();

        foreach ($this->extensions as $extension) {
            $container->registerExtension($extension);
        }

        foreach ($this->compilerPasses as $pass) {
            $container->addCompilerPass($pass);
        }

        $locator = new FileLocator('.');
        $loaders = [
            new YamlFileLoader($container, $locator),
            new XmlFileLoader($container, $locator),
        ];
        $delegatingLoader = new DelegatingLoader(new LoaderResolver($loaders));
        $delegatingLoader->load($this->configFile);

        // set the production soap metadata
        $container->setParameter('goetas_webservices.soap_client.metadata', $metadata);

        $container->compile();

        return $container;
    }

    /**
     * @return array
     */
    protected function fetchMetadata(ContainerInterface $debugContainer): array
    {
        $metadataReader = $debugContainer->get('goetas_webservices.soap_client.metadata_loader.dev');
        $wsdlMetadata = $debugContainer->getParameter('goetas_webservices.soap_client.config')['metadata'];
        $metadata = [];
        foreach (array_keys($wsdlMetadata) as $uri) {
            $metadata[$uri] = $metadataReader->load($uri);
        }

        return $metadata;
    }

    public function getDebugContainer(): ContainerBuilder
    {
        return $this->getContainerBuilder();
    }

    public function getProdContainer(): ContainerInterface
    {
        $ref = new \ReflectionClass(sprintf('%s\\%s', $this->classNs, $this->className));

        return $ref->newInstance();
    }

    public function dumpContainerForProd(string $dir, ContainerInterface $debugContainer): void
    {
        $metadata = $this->fetchMetadata($debugContainer);

        if (! $metadata) {
            throw new \Exception('Empty metadata can not be used for production');
        }

        $forProdContainer = $this->getContainerBuilder($metadata);
        $this->dump($forProdContainer, $dir);
    }

    private function dump(ContainerBuilder $container, string $dir): void
    {
        $dumper = new PhpDumper($container);
        $options = [
            'debug' => false,
            'class' => $this->className,
            'namespace' => $this->classNs,
        ];

        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($dir . '/' . $this->className . '.php', $dumper->dump($options));
    }

    public static function createSerializerBuilderFromContainer(ContainerInterface $container, ?callable $callback = null, ?string $metadataDirPrefix = null): SerializerBuilder
    {
        $destinations = $container->getParameter('goetas_webservices.soap_client.config')['destinations_jms'];

        if (null !== $metadataDirPrefix) {
            $destinations = array_map(static function ($dir) use ($metadataDirPrefix) {
                return rtrim($metadataDirPrefix, '/') . '/' . $dir;
            }, $destinations);
        }

        return self::createSerializerBuilder($destinations, $callback);
    }

    /**
     * @param array $jmsMetadata
     */
    public static function createSerializerBuilder(array $jmsMetadata, ?callable $callback = null): SerializerBuilder
    {
        $jmsMetadata = array_merge(self::getMetadataForSoapEnvelope(), $jmsMetadata);

        $serializerBuilder = SerializerBuilder::create();
        $serializerBuilder->configureHandlers(static function (HandlerRegistryInterface $handler) use ($callback, $serializerBuilder): void {
            $serializerBuilder->addDefaultHandlers();
            $handler->registerSubscribingHandler(new BaseTypesHandler()); // XMLSchema List handling
            $handler->registerSubscribingHandler(new XmlSchemaDateHandler()); // XMLSchema date handling
            $handler->registerSubscribingHandler(new FaultHandler());
            $handler->registerSubscribingHandler(new HeaderHandler());
            if ($callback) {
                call_user_func($callback, $handler);
            }
        });

        foreach ($jmsMetadata as $php => $dir) {
            $serializerBuilder->addMetadataDir($dir, $php);
        }

        return $serializerBuilder;
    }

    /**
     * @return string[]
     */
    public static function getMetadataForSoapEnvelope(): array
    {
        return self::$metadataForSoapEnvelope;
    }
}
