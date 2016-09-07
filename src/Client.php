<?php
namespace GoetasWebservices\SoapServices\SoapClient;

use GoetasWebservices\SoapServices\SoapClient\Arguments\ArgumentsReader;
use GoetasWebservices\SoapServices\SoapClient\Arguments\ArgumentsReaderInterface;
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
    use SoapCommon\ResultWrapperTrait;
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


    public function __construct(array $serviceDefinition, Serializer $serializer, MessageFactory $messageFactory, HttpClient $client, $unwrap = false)
    {
        $this->serviceDefinition = $serviceDefinition;
        $this->serializer = $serializer;

        $this->client = $client;
        $this->messageFactory = $messageFactory;
        $this->argumentsReader = new ArgumentsReader($this->serializer);
        $this->resultCreator = new ResultCreator($this->serializer, $unwrap);
    }

    public function __call($functionName, array $args)
    {
        $soapOperation = $this->findOperation($functionName, $this->serviceDefinition);

        $object = $this->argumentsReader->readArguments($args, $soapOperation['input']);

        $message = $this->wrapResult($this->serializer, $object, $soapOperation['input']['message_fqcn']);

        $xmlMessage = $this->serializer->serialize($message, 'xml');
        $headers = $this->buildHeaders($soapOperation);

        $request = $this->messageFactory->createRequest('POST', $this->serviceDefinition['endpoint'], $headers, $xmlMessage);

        try {
            $response = $this->client->sendRequest($request);
            if (strpos($response->getHeaderLine('Content-Type'), 'text/xml') !== 0) {
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
            $response = $this->serializer->deserialize((string)$response->getBody(), $soapOperation['output']['message_fqcn'], 'xml');
        } catch (HttpException $e) {

            if (strpos($e->getResponse()->getHeaderLine('Content-Type'), 'text/xml') === 0) {
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
            'Soap-Action' => '"' . $operation['action'] . '"',
        ];
    }

    protected function findOperation($functionName, array $serviceDefinition)
    {
        if (isset($serviceDefinition['operations'][$functionName])) {
            return $serviceDefinition['operations'][$functionName];
        }

        foreach ($serviceDefinition['operations'] as $opName => $operation) {
            if (strtolower($functionName) == strtolower($opName)) {
                return $opName;
            }
        }
        throw new ClientException("Can not find an operation to run $functionName service call");
    }
}
