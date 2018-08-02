<?php

namespace GoetasWebservices\SoapServices\SoapClient;

use GoetasWebservices\SoapServices\SoapClient\Arguments\ArgumentsReader;
use GoetasWebservices\SoapServices\SoapClient\Arguments\ArgumentsReaderInterface;
use GoetasWebservices\SoapServices\SoapClient\Arguments\Headers\Handler\HeaderHandler;
use GoetasWebservices\SoapServices\SoapClient\Envelope\SoapEnvelope\Messages\Fault as Fault11;
use GoetasWebservices\SoapServices\SoapClient\Envelope\SoapEnvelope12\Messages\Fault as Fault12;
use GoetasWebservices\SoapServices\SoapClient\Exception\ClientException;
use GoetasWebservices\SoapServices\SoapClient\Exception\ServerException;
use GoetasWebservices\SoapServices\SoapClient\Exception\SoapException;
use GoetasWebservices\SoapServices\SoapClient\Exception\UnexpectedFormatException;
use GoetasWebservices\SoapServices\SoapClient\Result\ResultCreator;
use GoetasWebservices\SoapServices\SoapClient\Result\ResultCreatorInterface;
use GoetasWebservices\SoapServices\SoapEnvelope;
use Http\Client\Exception\HttpException;
use Http\Client\HttpClient;
use Http\Message\MessageFactory;
use JMS\Serializer\Serializer;
use Psr\Http\Message\ResponseInterface;

class Client
{
    /**
     * @var Serializer
     */
    protected $serializer;
    /**
     * @var array
     */
    protected $serviceDefinition;
    /**
     * @var HttpClient
     */
    protected $client;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var ResultCreatorInterface
     */
    private $resultCreator;

    /**
     * @var ArgumentsReaderInterface
     */
    private $argumentsReader;


    public function __construct(array $serviceDefinition, Serializer $serializer, MessageFactory $messageFactory, HttpClient $client, HeaderHandler $headerHandler)
    {
        $this->serviceDefinition = $serviceDefinition;
        $this->serializer = $serializer;

        $this->client = $client;
        $this->messageFactory = $messageFactory;
        $this->argumentsReader = new ArgumentsReader($this->serializer, $headerHandler);
        $this->resultCreator = new ResultCreator($this->serializer, !empty($serviceDefinition['unwrap']));
    }

    public function __call($functionName, array $args)
    {
        $soapOperation = $this->findOperation($functionName, $this->serviceDefinition);
        $message = $this->argumentsReader->readArguments($args, $soapOperation['input']);

        $xmlMessage = $this->serializer->serialize($message, 'xml');
        $headers = $this->buildHeaders($soapOperation);
        $request = $this->messageFactory->createRequest('POST', $this->serviceDefinition['endpoint'], $headers, $xmlMessage);

        try {
            $response = $this->client->sendRequest($request);
            if (strpos($response->getHeaderLine('Content-Type'), 'xml') === false) {
                throw new UnexpectedFormatException(
                    $response,
                    $request,
                    "Unexpected content type '" . $response->getHeaderLine('Content-Type') . "'"
                );
            }

            // fast return if no return is expected
            if (!count($soapOperation['output']['parts'])) {
                return null;
            }

            $body = (string)$response->getBody();

            $faultClass = $this->findFaultClass($response);

            if (strpos($body, ':Fault>') !== false) { // some server returns a fault with 200 OK HTTP
                $fault = $this->serializer->deserialize($body, $faultClass, 'xml');
                throw $fault->createException($response, $request);
            }

            $response = $this->serializer->deserialize($body, $soapOperation['output']['message_fqcn'], 'xml');
        } catch (HttpException $e) {
            if (strpos($e->getResponse()->getHeaderLine('Content-Type'), 'xml') !== false) {
                $faultClass = $this->findFaultClass($e->getResponse());
                $fault = $this->serializer->deserialize((string)$e->getResponse()->getBody(), $faultClass, 'xml');
                throw $fault->createException($e->getResponse(), $e->getRequest(), $e);
            } else {
                throw new ServerException(
                    $e->getResponse(),
                    $e->getRequest(),
                    $e
                );
            }
        }
        return $this->resultCreator->prepareResult($response, $soapOperation['output']);
    }

    public function findFaultClass(ResponseInterface $response)
    {
        if (strpos($response->getHeaderLine('Content-Type'), 'application/soap+xml') === 0) {
            return Fault12::class;
        } else {
            return Fault11::class;
        }
    }

    protected function buildHeaders(array $operation)
    {
        return [
            'Content-Type' => isset($operation['version']) && $operation['version'] === '1.2'
                ? 'application/soap+xml; charset=utf-8'
                : 'text/xml; charset=utf-8',
            'SoapAction' => '"' . $operation['action'] . '"',
        ];
    }

    protected function findOperation($functionName, array $serviceDefinition)
    {
        if (isset($serviceDefinition['operations'][$functionName])) {
            return $serviceDefinition['operations'][$functionName];
        }

        foreach ($serviceDefinition['operations'] as $operation) {
            if (strtolower($functionName) == strtolower($operation['method'])) {
                return $operation;
            }
        }
        throw new ClientException("Can not find an operation to run $functionName service call");
    }
}
