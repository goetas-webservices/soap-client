<?php
namespace GoetasWebservices\SoapServices\SoapClient;

use GoetasWebservices\SoapServices\SoapClient\Message\DiactorosFactory;
use GoetasWebservices\SoapServices\SoapClient\Message\GuzzleFactory;
use GoetasWebservices\SoapServices\SoapClient\Message\MessageFactoryInterface;
use GoetasWebservices\SoapServices\SoapCommon\Metadata\PhpMetadataGenerator;
use GoetasWebservices\SoapServices\SoapCommon\Metadata\PhpMetadataGeneratorInterface;
use GoetasWebservices\XML\WSDLReader\Exception\PortNotFoundException;
use GoetasWebservices\XML\WSDLReader\Exception\ServiceNotFoundException;
use GuzzleHttp\ClientInterface;
use JMS\Serializer\SerializerInterface;

class ClientFactory
{
    protected $namespaces = [];
    protected $metadata = [];
    /**
     * @var SerializerInterface
     */
    protected $serializer;
    /**
     * @var MessageFactoryInterface
     */
    protected $messageFactory;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var PhpMetadataGeneratorInterface
     */
    private $generator;

    private $unwrap = false;

    public function __construct(array $namespaces, SerializerInterface $serializer)
    {
        $this->setSerializer($serializer);

        foreach ($namespaces as $namespace => $phpNamespace) {
            $this->addNamespace($namespace, $phpNamespace);
        }
    }

    public function setUnwrapResponses($unwrap)
    {
        $this->unwrap = !!$unwrap;
    }

    public function setHttpClient(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param MessageFactoryInterfaceFactory $messageFactory
     */
    public function setMessageFactory(MessageFactoryInterfaceFactory $messageFactory)
    {
        $this->messageFactory = $messageFactory;
    }

    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function setMetadataGenerator(PhpMetadataGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    private function getSoapService($wsdl, $portName = null, $serviceName = null)
    {
        $generator = $this->generator ?: new PhpMetadataGenerator();

        foreach ($this->namespaces as $ns => $phpNs) {
            $generator->addNamespace($ns, $phpNs);
        }

        $services = $generator->generateServices($wsdl);
        $service = $this->getService($serviceName, $services);

        return $this->getPort($portName, $service);
    }

    public function addNamespace($uri, $phpNs)
    {
        $this->namespaces[$uri] = $phpNs;
    }

    /**
     * @param string $wsdl
     * @param null|string $portName
     * @param null|string $serviceName
     * @param null|bool $unwrap
     * @return Client
     */
    public function getClient($wsdl, $portName = null, $serviceName = null, $unwrap = null)
    {
        $service = $this->getSoapService($wsdl, $portName, $serviceName);

        $this->client = $this->client ?: new \GuzzleHttp\Client();
        $this->messageFactory = $this->messageFactory ?: self::getDefaultHttpFactory();
        $unwrap = is_null($unwrap) ? $this->unwrap : $unwrap;

        return new Client($service, $this->serializer, $this->messageFactory, $this->client, $unwrap);
    }

    private static function getDefaultHttpFactory()
    {
        if (class_exists('GuzzleHttp\Psr7\Request')) {
            return new GuzzleFactory();
        }
        if (class_exists('Zend\Diactoros\Request')) {
            return new DiactorosFactory();
        }
        throw new \Exception("Can not find a PSR-7 compatible implementation");
    }

    /**
     * @param $serviceName
     * @param array $services
     * @return array
     * @throws ServiceNotFoundException
     */
    private function getService($serviceName, array $services)
    {
        if ($serviceName && isset($services[$serviceName])) {
            return $services[$serviceName];
        } elseif ($serviceName) {
            throw new ServiceNotFoundException("The service named $serviceName can not be found");
        } else {
            return reset($services);
        }
    }

    /**
     * @param string $portName
     * @param array $service
     * @return array
     * @throws PortNotFoundException
     */
    private function getPort($portName, array $service)
    {
        if ($portName && isset($service[$portName])) {
            return $service[$portName];
        } elseif ($portName) {
            throw new PortNotFoundException("The port named $portName can not be found");
        } else {
            return reset($service);
        }
    }
}
