<?php

declare(strict_types=1);

namespace GoetasWebservices\SoapServices\SoapClient;

use GoetasWebservices\SoapServices\Metadata\Arguments\ArgumentsReader;
use GoetasWebservices\SoapServices\Metadata\Arguments\ArgumentsReaderInterface;
use GoetasWebservices\SoapServices\Metadata\Envelope\SoapEnvelope\Messages\Fault as Fault11;
use GoetasWebservices\SoapServices\Metadata\Envelope\SoapEnvelope12\Messages\Fault as Fault12;
use GoetasWebservices\SoapServices\Metadata\Headers\Header;
use GoetasWebservices\SoapServices\Metadata\Headers\HeadersIncoming;
use GoetasWebservices\SoapServices\Metadata\Headers\HeadersOutgoing;
use GoetasWebservices\SoapServices\SoapClient\Exception\ClientException;
use GoetasWebservices\SoapServices\SoapClient\Exception\Fault11Exception;
use GoetasWebservices\SoapServices\SoapClient\Exception\Fault12Exception;
use GoetasWebservices\SoapServices\SoapClient\Exception\FaultException;
use GoetasWebservices\SoapServices\SoapClient\Exception\UnexpectedFormatException;
use GoetasWebservices\SoapServices\SoapClient\Result\ResultCreator;
use GoetasWebservices\SoapServices\SoapClient\Result\ResultCreatorInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Client
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;
    /**
     * @var array
     */
    protected $serviceDefinition;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var RequestFactoryInterface
     */
    protected $messageFactory;


    /**
     * @var StreamFactoryInterface
     */
    protected $streamFactory;


    /**
     * @var ResultCreatorInterface
     */
    private $resultCreator;

    /**
     * @var ArgumentsReaderInterface
     */
    private $argumentsReader;

    /**
     * @var bool
     */
    private $debug = false;

    /**
     * Debug
     *
     * @var RequestInterface
     */
    private $requestMessage;

    /**
     * Debug
     *
     * @var ResponseInterface
     */
    private $responseMessage;

    public function __construct(array $serviceDefinition, SerializerInterface $serializer, RequestFactoryInterface $messageFactory, StreamFactoryInterface $streamFactory, ClientInterface $client)
    {
        $this->serviceDefinition = $serviceDefinition;
        $this->serializer = $serializer;

        $this->client = $client;
        $this->messageFactory = $messageFactory;
        $this->streamFactory = $streamFactory;
        $this->argumentsReader = new ArgumentsReader($this->serializer);
        $this->resultCreator = new ResultCreator($this->serializer, !empty($serviceDefinition['unwrap']));
    }

    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    private function extractHeaders(array $args): array
    {
        $headers = [];
        foreach ($args as $k => $arg) {
            if ($arg instanceof Header) {
                $headers[] = $arg;
                unset($args[$k]);
            }
        }

        return [$args, $headers];
    }

    /**
     * @param array $args
     *
     * @return mixed
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingReturnTypeHint
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.UselessReturnAnnotation
     */
    public function __call(string $functionName, array $args)
    {
        [$args, $headers] = $this->extractHeaders($args);
        $soapOperation = $this->findOperation($functionName, $this->serviceDefinition);
        $message = $this->argumentsReader->readArguments($args, $soapOperation['input']);

        $bag = new HeadersOutgoing($headers);
        $context = SerializationContext::create()->setAttribute('headers_outgoing', $bag);
        $xmlMessage = $this->serializer->serialize($message, 'xml', $context);

        $requestMessage = $this->createRequestMessage($xmlMessage, $soapOperation);

        $responseMessage = $this->client->sendRequest($requestMessage);

        if ($this->debug) {
            $this->responseMessage = $responseMessage;
        }

        $this->assertValidResponse($responseMessage, $requestMessage);

        // fast return if no return is expected
        // @todo but we should still check for headers...
        if (!count($soapOperation['output']['parts'])) {
            return null;
        }

        if (200 !== $responseMessage->getStatusCode()) {
            throw $this->createFaultException($responseMessage, $requestMessage);
        }

        $body = (string) $responseMessage->getBody();
        if (false !== strpos($body, ':Fault>')) { // some server returns a fault with 200 OK HTTP
            throw $this->createFaultException($responseMessage, $requestMessage);
        }

        $bag = new HeadersIncoming();
        $context = DeserializationContext::create()->setAttribute('headers_incoming', $bag);
        $response = $this->serializer->deserialize($body, $soapOperation['output']['message_fqcn'], 'xml', $context);

        return $this->resultCreator->prepareResult($response, $soapOperation['output']);
    }

    public function assertValidResponse(ResponseInterface $responseMessage, RequestInterface $requestMessage, ?\Throwable $previous = null): void
    {
        if (false === strpos($responseMessage->getHeaderLine('Content-Type'), 'xml')) {
            throw new UnexpectedFormatException(
                "Unexpected content type '" . $responseMessage->getHeaderLine('Content-Type') . "'",
                $responseMessage,
                $requestMessage,
                $previous
            );
        }

        $body = (string) $responseMessage->getBody();

        if (false === strpos($body, 'http://schemas.xmlsoap.org/soap/envelope/') && false === strpos($body, 'http://www.w3.org/2003/05/soap-envelope')) {
            throw new UnexpectedFormatException(
                'Unexpected content, invalid SOAP message',
                $responseMessage,
                $requestMessage,
                $previous
            );
        }
    }

    private function createFaultException(ResponseInterface $response, RequestInterface $request): FaultException
    {
        [$faultException, $faultClass] = $this->findFaultClass($response);

        $fault = null;
        if (null !== $faultClass) {
            $bag = new HeadersIncoming();
            $context = DeserializationContext::create()->setAttribute('headers_incoming', $bag);
            $fault = $this->serializer->deserialize((string) $response->getBody(), $faultClass, 'xml', $context);
        }

        return $faultException::createFromResponse($response, $request, $fault);
    }

    /**
     * @return string[]
     */
    protected function findFaultClass(ResponseInterface $response): array
    {
        if (false === strpos((string) $response->getBody(), ':Fault>')) {
            return [FaultException::class, null];
        }

        if (false !== strpos($response->getHeaderLine('Content-Type'), 'application/soap+xml')) {
            return [Fault12Exception::class, Fault12::class];
        }

        if (false !== strpos($response->getHeaderLine('Content-Type'), 'xml')) {
            return [Fault11Exception::class, Fault11::class];
        }
    }

    public function __getLastRequestMessage(): ?RequestInterface
    {
        return $this->requestMessage;
    }

    public function __getLastResponseMessage(): ?ResponseInterface
    {
        return $this->responseMessage;
    }

    /**
     * @return string[]
     */
    protected function buildHeaders(array $operation): array
    {
        return [
            'Content-Type' => isset($operation['version']) && '1.2' === $operation['version']
                ? 'application/soap+xml; charset=utf-8'
                : 'text/xml; charset=utf-8',
            'SoapAction' => '"' . $operation['action'] . '"',
        ];
    }

    protected function findOperation(string $functionName, array $serviceDefinition): array
    {
        if (isset($serviceDefinition['operations'][$functionName])) {
            return $serviceDefinition['operations'][$functionName];
        }

        foreach ($serviceDefinition['operations'] as $operation) {
            if (strtolower($functionName) === strtolower($operation['method'])) {
                return $operation;
            }
        }

        throw new ClientException(sprintf('Can not find an operation to run %s service call', $functionName));
    }

    private function createRequestMessage(string $xmlMessage, array $soapOperation): RequestInterface
    {
        $headers = $this->buildHeaders($soapOperation);
        $requestMessage = $this->messageFactory
            ->createRequest('POST', $this->serviceDefinition['endpoint'])
            ->withBody($this->streamFactory->createStream($xmlMessage));
        foreach ($headers as $name => $val) {
            $requestMessage = $requestMessage->withHeader($name, $val);
        }

        if ($this->debug) {
            $this->requestMessage = $requestMessage;
        }

        return $requestMessage;
    }
}
