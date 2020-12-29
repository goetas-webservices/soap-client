<?php

declare(strict_types=1);

namespace GoetasWebservices\SoapServices\SoapClient;

use GoetasWebservices\SoapServices\Metadata\Loader\MetadataLoaderInterface;
use GoetasWebservices\SoapServices\Metadata\MetadataUtils;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use JMS\Serializer\SerializerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class ClientFactory
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;
    /**
     * @var RequestFactoryInterface
     */
    protected $messageFactory;

    /**
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * @var MetadataLoaderInterface
     */
    private $reader;

    /**
     * @var StreamFactoryInterface
     */
    private $streamFactory;

    public function __construct(MetadataLoaderInterface $reader, SerializerInterface $serializer)
    {
        $this->setMetadataReader($reader);
        $this->setSerializer($serializer);
    }

    public function setHttpClient(ClientInterface $client): void
    {
        $this->httpClient = $client;
    }

    public function setMessageFactory(RequestFactoryInterface $messageFactory): void
    {
        $this->messageFactory = $messageFactory;
    }

    public function setSerializer(SerializerInterface $serializer): void
    {
        $this->serializer = $serializer;
    }

    public function setMetadataReader(MetadataLoaderInterface $reader): void
    {
        $this->reader = $reader;
    }

    private function getSoapService(string $wsdl, ?string $portName = null, ?string $serviceName = null): array
    {
        $servicesMetadata = $this->reader->load($wsdl);
        $service = MetadataUtils::getService($serviceName, $servicesMetadata);

        return MetadataUtils::getPort($portName, $service);
    }

    public function getClient(string $wsdl, ?string $portName = null, ?string $serviceName = null): Client
    {
        $service = $this->getSoapService($wsdl, $portName, $serviceName);

        $this->httpClient = $this->httpClient ?: Psr18ClientDiscovery::find();
        $this->messageFactory = $this->messageFactory ?: Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $this->streamFactory ?: Psr17FactoryDiscovery::findStreamFactory();

        return new Client($service, $this->serializer, $this->messageFactory, $this->streamFactory, $this->httpClient);
    }
}
