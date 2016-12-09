<?php
namespace GoetasWebservices\SoapServices\SoapClient;

use GoetasWebservices\SoapServices\SoapClient\Arguments\ArgumentsReader;
use GoetasWebservices\SoapServices\SoapClient\Arguments\ArgumentsReaderInterface;
use GoetasWebservices\SoapServices\SoapClient\Arguments\Headers\Handler\HeaderHandler;
use GoetasWebservices\SoapServices\SoapClient\Exception\ClientException;
use GoetasWebservices\SoapServices\SoapClient\Exception\FaultException;
use GoetasWebservices\SoapServices\SoapClient\Exception\ServerException;
use GoetasWebservices\SoapServices\SoapClient\Exception\SoapException;
use GoetasWebservices\SoapServices\SoapClient\Result\ResultCreator;
use GoetasWebservices\SoapServices\SoapClient\Result\ResultCreatorInterface;
use GoetasWebservices\SoapServices\SoapCommon as SoapCommon;
use GoetasWebservices\SoapServices\SoapCommon\SoapEnvelope\Parts\Fault;
use GoetasWebservices\SoapServices\SoapEnvelope;
use Http\Client\Exception\HttpException;
use Http\Client\HttpClient;
use Http\Message\MessageFactory;
use JMS\Serializer\Serializer;

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
            $xmlResponse = strpos($response->getHeaderLine('Content-Type'), 'text/xml') === 0;
            $soapXmlResponse = strpos($response->getHeaderLine('Content-Type'), 'application/soap+xml') === 0;
            if (!$xmlResponse && !$soapXmlResponse) {
                throw new ServerException(
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
            if (strpos($body, ':Fault>')!==false) {
                $fault = $this->serializer->deserialize($body, Fault::class, 'xml');
                throw new FaultException(
                    $fault,
                    $response,
                    $request,
                    "SOAP Fault",
                    null,
                    new \Exception()
                );
            }

            $response = $this->serializer->deserialize($body, $soapOperation['output']['message_fqcn'], 'xml');
        } catch (HttpException $e) {

            $xmlResponse = strpos($e->getResponse()->getHeaderLine('Content-Type'), 'text/xml') === 0;
            $soapXmlResponse = strpos($e->getResponse()->getHeaderLine('Content-Type'), 'application/soap+xml') === 0;
            if ($xmlResponse || $soapXmlResponse) {
                $fault = $this->serializer->deserialize((string)$e->getResponse()->getBody(), Fault::class, 'xml');
                throw new FaultException(
                    $fault,
                    $e->getResponse(),
                    $e->getRequest(),
                    $e->getMessage(),
                    null,
                    $e
                );
            } else {
                throw new ServerException(
                    $e->getResponse(),
                    $e->getRequest(),
                    $e->getMessage(),
                    null,
                    $e
                );
            }
        }

        return $this->resultCreator->prepareResult($response, $soapOperation['output']);
    }

    protected function buildHeaders(array $operation)
    {
        return [
            'Content-Type' => 'text/xml; charset=utf-8',
            'SoapAction' => '"' . $operation['action'] . '"',
        ];
    }

    protected function findOperation($functionName, array $serviceDefinition)
    {
        if (isset($serviceDefinition['operations'][$functionName])) {
            return $serviceDefinition['operations'][$functionName];
        }

        foreach ($serviceDefinition['operations'] as $opName => $operation) {
            if (strtolower($functionName) == strtolower($opName)) {
                return $operation;
            }
        }
        throw new ClientException("Can not find an operation to run $functionName service call");
    }
}
