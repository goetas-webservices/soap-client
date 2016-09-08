<?php
namespace GoetasWebservices\SoapServices\SoapClient;

use GoetasWebservices\SoapServices\SoapClient\Arguments\Headers\Handler\HeaderHandler;
use GoetasWebservices\SoapServices\SoapCommon\Metadata\PhpMetadataGenerator;
use GoetasWebservices\SoapServices\SoapCommon\Metadata\PhpMetadataGeneratorInterface;
use GoetasWebservices\XML\WSDLReader\Exception\PortNotFoundException;
use GoetasWebservices\XML\WSDLReader\Exception\ServiceNotFoundException;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\MessageFactory;
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
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @var PhpMetadataGeneratorInterface
     */
    private $generator;

    private $unwrap = false;

    /**
     * @var HeaderHandler
     */
    private $headerHandler;

    public function __construct(array $namespaces, SerializerInterface $serializer)
    {
        $this->setSerializer($serializer);

        foreach ($namespaces as $namespace => $phpNamespace) {
            $this->addNamespace($namespace, $phpNamespace);
        }
    }

    public function setHeaderHandler(HeaderHandler $headerHandler)
    {
        $this->headerHandler = $headerHandler;
    }

    public function setUnwrapResponses($unwrap)
    {
        $this->unwrap = !!$unwrap;
    }

    public function setHttpClient(HttpClient $client)
    {
        $this->httpClient = $client;
    }

    /**
     * @param MessageFactory $messageFactory
     */
    public function setMessageFactory(MessageFactory $messageFactory)
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

        $this->httpClient = $this->httpClient ?: HttpClientDiscovery::find();
        $this->messageFactory = $this->messageFactory ?: MessageFactoryDiscovery::find();
        $headerHandler = $this->headerHandler ?: new HeaderHandler();
        $unwrap = is_null($unwrap) ? $this->unwrap : $unwrap;

        return new Client($service, $this->serializer, $this->messageFactory, $this->httpClient, $headerHandler, $unwrap);
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
