goetas-webservices / soap-client
================================

[![Build Status](https://travis-ci.org/goetas-webservices/soap-client.svg?branch=master)](https://travis-ci.org/goetas-webservices/soap-client)
[![Code Coverage](https://scrutinizer-ci.com/g/goetas-webservices/soap-client/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/goetas-webservices/soap-client/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/goetas-webservices/soap-client/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/goetas-webservices/soap-client/?branch=master)


Pure PHP implementation of SOAP 1.1 client


Installation
-----------

The recommended way to install goetas-webservices / soap-client is using [Composer](https://getcomposer.org/):

Add this packages to your `composer.json` file.

```
{
    "require": {
        "goetas-webservices/soap-client": "^0.1",
    },
    "require-dev": {
        "goetas-webservices/wsdl2php": "^0.2",
    },
}
```

# How to


To improve performance, this library is based on the concept that all the SOAP/WSDL 
metadata has to be compiled into PHP compatible metadata (in reality is a bib plain PHP array,
so is really fast).

To do this we have to define a configuration file (in this case called `config.yml`) that
holds some important information. 

Here is an example:

```yml
# config.yml

soap_client:
  alternative_endpoints:
    MyServiceName:
      MySoapPortName: http://localhost:8080/service

xsd2php:
  namespaces:
    'http://www.example.org/test/': 'TestNs/MyApp'
  destinations_php:
    'TestNs/MyApp': soap/src
  destinations_jms:
    'TestNs/MyApp': soap/metadata
  aliases:
    'http://www.example.org/test/':
      MyCustomXSDType:  'MyCustomMappedPHPType'

wsdl2php:
  metadata:
    'test.wsdl': ~
    'http://www.webservicex.net/weather.asmx?WSDL': ~
```

This file has some important sections: 

### SOAP Specific
* `soap_client.alternative_endpoints` (optional) allows you to specify alternative URLs that can be used
 when developing your integration. 
 If this parameter is not present, will be used the URL defined by the WSDL file, 
 but if is set, will be used the specified URL for the service called 
 *MyServiceName* and on *MySoapPortName* port.


* `soap_client.unwrap_returns` (optional, default: *false*) allows to define the "wrapped" SOAP services mode. 
 Instructs the client to "unwrap" all the returns.

### WSDL Specific

* `wsdl2php.metadata` specifies where are placed WSDL files that will be used to generate al the required PHP metadata.

 
### XML/XSD Specific
 
* `xsd2php.namespaces` (required) defines the mapping between XML namespaces and PHP namespaces.
 (in the example we have the `http://www.example.org/test/` XML namespace mapped to `TestNs\MyApp`)


* `xsd2php.destinations_php` (required) specifies the directory where to save the PHP classes that belongs to 
 `TestNs\MyApp` PHP namespace. (in this example `TestNs\MyApp` classes will ne saved into `soap/src` directory.
 

* `xsd2php.destinations_jms` (required) specifies the directory where to save JMS Serializer metadata files 
 that belongs to `TestNs\MyApp` PHP namespace. 
 (in this example `TestNs\MyApp` metadata will ne saved into `soap/metadata` directory.
 
 
* `xsd2php.aliases` (optional) specifies some mappings that are handled by custom JMS serializer handlers.
 Allows to specify to do not generate metadata for some XML types, and assign them directly a PHP class.
 For that PHP class is necessary to create a custom JMS serialize/deserialize handler.
 
 
 
## Metadata generation
 
In order to be able to use the SOAP client we have to generate some metadata and PHP classes.
 
To do it we can run:

```sh
bin/soap-client generate \
 tests/config.yml \
 --dest-class=GlobalWeather/Container/SoapClientContainer \
 soap/src-gw/Container 
```


* `bin/soap-client generate` is the command we are running
* `tests/config.yml` is a path to our configuration file
* `--dest-class=GlobalWeather/Container/SoapClientContainer` allows to specify the fully qualified class name of the 
 container class that will hold all the webservice metadata.
* `soap/src/Container` is the path where to save the container class that holds all the webservice metadata
 (you will have to configure the auto loader to load  it)

 
 
## Using the client

Once all the metadata are generated we can use our SOAP client.

Let's see a minimal example:

```php
// composer auto loader
require __DIR__ . '/vendor/autoload.php';

// instantiate the main container class
// the name was defined by --dest-class=GlobalWeather/Container/SoapClientContainer
// parameter during the generation process
$container = new SoapClientContainer();

// create a JMS serializer instance
$serializer = SoapContainerBuilder::createSerializerBuilderFromContainer($container)->build();
// get the metadata from the container
$metadata = $container->get('goetas.soap_client.metadata_reader');

$factory = new ClientFactory($metadata, $serializer);

/**
 * @var $client \GlobalWeather\SoapStubs\WeatherSoap
 */
 // get the soap client
$client = $factory->getClient('http://www.webservicex.net/weather.asmx?WSDL');

// call the webservice
$result = $client->getWeather(2010, "May", "USA");
```


Please note the `@var $client \GlobalWeather\SoapStubs\WeatherSoap`. The generated metadata have also a "stub" class
that allows modern IDE to give you type hinting for parameters and return data.

This allows you to develop faster your client.
