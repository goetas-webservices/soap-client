<?php
namespace GoetasWebservices\SoapServices\SoapClient\Builder;

use GoetasWebservices\SoapServices\SoapClient\DependencyInjection\SoapClientExtension;
use GoetasWebservices\SoapServices\SoapClient\DependencyInjection\Compiler\CleanupPass;
use GoetasWebservices\SoapServices\SoapClient\Envelope\Handler\FaultHandler;
use GoetasWebservices\Xsd\XsdToPhpRuntime\Jms\Handler\BaseTypesHandler;
use GoetasWebservices\Xsd\XsdToPhpRuntime\Jms\Handler\XmlSchemaDateHandler;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\Handler\HandlerRegistryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class SoapContainerBuilder
{

    private $className = 'SoapClientContainer';

    private $classNs = 'SoapServicesStub';

    protected $configFile = 'config.yml';

    protected $extensions = [];

    protected $compilerPasses = [];

    protected static $metadataForSoapEnvelope = [
        'GoetasWebservices\SoapServices\SoapClient\Envelope\SoapEnvelope12' => __DIR__ . '/../Resources/metadata/jms12',
        'GoetasWebservices\SoapServices\SoapClient\Envelope\SoapEnvelope' => __DIR__ . '/../Resources/metadata/jms'
    ];

    /**
     *
     * @var LoggerInterface
     */
    private $logger;

    public function __construct($configFile = null, LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        $this->setConfigFile($configFile);
        $this->addExtension(new SoapClientExtension());
        $this->addCompilerPass(new CleanupPass());
    }

    public function setConfigFile($configFile)
    {
        $this->configFile = $configFile;
    }

    protected function addExtension(ExtensionInterface $extension)
    {
        $this->extensions[] = $extension;
    }

    protected function addCompilerPass(CompilerPassInterface $pass)
    {
        $this->compilerPasses[] = $pass;
    }

    public function setContainerClassName($fqcn)
    {
        $fqcn = strtr($fqcn, [
            '.' => '\\',
            '/' => '\\'
        ]);
        $pos = strrpos($fqcn, '\\');
        $this->className = substr($fqcn, $pos + 1);
        $this->classNs = substr($fqcn, 0, $pos);
    }

    /**
     *
     * @param array $metadata
     * @return ContainerBuilder
     */
    protected function getContainerBuilder(array $metadata = array())
    {
        $container = new ContainerBuilder();

        foreach ($this->extensions as $extension) {
            $container->registerExtension($extension);
        }

        foreach ($this->compilerPasses as $pass) {
            $container->addCompilerPass($pass);
        }

        $locator = new FileLocator('.');
        $loaders = array(
            new YamlFileLoader($container, $locator),
            new XmlFileLoader($container, $locator)
        );
        $delegatingLoader = new DelegatingLoader(new LoaderResolver($loaders));
        $delegatingLoader->load($this->configFile);

        // set the production soap metadata
        $container->setParameter('goetas_webservices.soap_client.metadata', $metadata);

        $container->compile();

        return $container;
    }

    /**
     *
     * @param ContainerInterface $debugContainer
     * @return array
     */
    protected function fetchMetadata(ContainerInterface $debugContainer)
    {
        $metadataReader = $debugContainer->get('goetas_webservices.soap_client.metadata_loader.dev');
        $wsdlMetadata = $debugContainer->getParameter('goetas_webservices.soap_client.config')['metadata'];
        $metadata = [];
        foreach (array_keys($wsdlMetadata) as $uri) {
            $metadata[$uri] = $metadataReader->load($uri);
        }

        return $metadata;
    }

    public function getDebugContainer()
    {
        return $this->getContainerBuilder();
    }

    /**
     *
     * @return ContainerInterface
     */
    public function getProdContainer()
    {
        $ref = new \ReflectionClass("{$this->classNs}\\{$this->className}");
        return $ref->newInstance();
    }

    /**
     *
     * @param
     *            $dir
     * @param ContainerInterface $debugContainer
     */
    public function dumpContainerForProd($dir, ContainerInterface $debugContainer)
    {
        $metadata = $this->fetchMetadata($debugContainer);

        if (! $metadata) {
            throw new \Exception("Empty metadata can not be used for production");
        }
        $forProdContainer = $this->getContainerBuilder($metadata);
        $this->dump($forProdContainer, $dir);
    }

    private function dump(ContainerBuilder $container, $dir)
    {
        $dumper = new PhpDumper($container);
        $options = [
            'debug' => false,
            'class' => $this->className,
            'namespace' => $this->classNs
        ];

        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($dir . '/' . $this->className . '.php', $dumper->dump($options));
    }

    /**
     * @param ContainerInterface $container
     * @param callable $callback
     * @param string $metadataDirPrefix
     * @return SerializerBuilder
     */
    public static function createSerializerBuilderFromContainer(ContainerInterface $container, callable $callback = null, $metadataDirPrefix = null)
    {
        $destinations = $container->getParameter('goetas_webservices.soap_client.config')['destinations_jms'];

        if ($metadataDirPrefix !== null) {
            $destinations = array_map(function ($dir) use ($metadataDirPrefix) {
                return rtrim($metadataDirPrefix, '/') . '/' . $dir;
            }, $destinations);
        }

        return self::createSerializerBuilder($destinations, $callback);
    }

    /**
     *
     * @param array $jmsMetadata
     * @param callable $callback
     * @return SerializerBuilder
     */
    public static function createSerializerBuilder(array $jmsMetadata, callable $callback = null)
    {
        $jmsMetadata = array_merge(self::getMetadataForSoapEnvelope(), $jmsMetadata);

        $serializerBuilder = SerializerBuilder::create();
        $serializerBuilder->configureHandlers(function (HandlerRegistryInterface $handler) use ($callback, $serializerBuilder) {
            $serializerBuilder->addDefaultHandlers();
            $handler->registerSubscribingHandler(new BaseTypesHandler()); // XMLSchema List handling
            $handler->registerSubscribingHandler(new XmlSchemaDateHandler()); // XMLSchema date handling
            $handler->registerSubscribingHandler(new FaultHandler());
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
     *
     * @return string[]
     */
    public static function getMetadataForSoapEnvelope()
    {
        return self::$metadataForSoapEnvelope;
    }
}
