# goetas-webservices / soap-client

[![Build Status](https://travis-ci.org/goetas-webservices/soap-client.svg?branch=master)](https://travis-ci.org/goetas-webservices/soap-client)

PHP implementation of SOAP 1.1 and 1.2 client specifications.

Strengths: 

- Pure PHP, no dependencies on `ext-soap`
- Extensible (JMS event listeners support)
- PSR-7 HTTP messaging
- PSR-17 HTTP messaging factories
- PSR-18 HTTP Client
- No WSDL/XSD parsing on production
- IDE type hinting support

Only document/literal style is supported and the webservice should follow
the [WS-I](https://en.wikipedia.org/wiki/WS-I_Basic_Profile) guidelines.

There are no plans to support the deprecated rpc and encoded styles.
Webservices not following the WS-I specifications might work, but they are officially not supported.

## Demo 

[goetas-webservices/soap-client-demo](https://github.com/goetas-webservices/soap-client-demo) is a demo project
that shows how to consume a SOAP api in a generic PHP web application.


Installation
-----------

The recommended way to install goetas-webservices / soap-client is using [Composer](https://getcomposer.org/):

Add this packages to your `composer.json` file.

```
{
    "require": {
        "goetas-webservices/soap-client": "^0.3",
    },
    "require-dev": {
        "goetas-webservices/wsdl2php": "^0.5.1",
    },
}
```

# How to

To improve performance, this library is based on the concept that all the SOAP/WSDL 
metadata has to be compiled into PHP compatible metadata (in reality is a big plain PHP array,
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

  namespaces:
    'http://www.example.org/test/': 'TestNs/MyApp'
  destinations_php:
    'TestNs/MyApp': soap/src
  destinations_jms:
    'TestNs/MyApp': soap/metadata
  aliases:
    'http://www.example.org/test/':
      MyCustomXSDType:  'MyCustomMappedPHPType'

  metadata:
    'test.wsdl': ~
    'http://www.webservicex.net/weather.asmx?WSDL': ~
```

This file has some important sections: 

### SOAP Specific
* `alternative_endpoints` (optional) allows you to specify alternative URLs that can be used
 when developing your integration. 
 If this parameter is not present, will be used the URL defined by the WSDL file, 
 but if is set, will be used the specified URL for the service called 
 *MyServiceName* and on *MySoapPortName* port.


* `unwrap_returns` (optional, default: *false*) allows to define the "wrapped" SOAP services mode. 
 Instructs the client to "unwrap" all the returns.

### WSDL Specific

* `metadata` specifies where are placed WSDL files that will be used to generate al the required PHP metadata.

 
### XML/XSD Specific
 
* `namespaces` (required) defines the mapping between XML namespaces and PHP namespaces.
 (in the example we have the `http://www.example.org/test/` XML namespace mapped to `TestNs\MyApp`)


* `destinations_php` (required) specifies the directory where to save the PHP classes that belongs to 
 `TestNs\MyApp` PHP namespace. (in this example `TestNs\MyApp` classes will ne saved into `soap/src` directory.
 

* `destinations_jms` (required) specifies the directory where to save JMS Serializer metadata files 
 that belongs to `TestNs\MyApp` PHP namespace. 
 (in this example `TestNs\MyApp` metadata will ne saved into `soap/metadata` directory.
 
 
* `aliases` (optional) specifies some mappings that are handled by custom JMS serializer handlers.
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
$metadata = $container->get('goetas_webservices.soap.metadata_reader');

$factory = new ClientFactory($metadata, $serializer);

/**
 * @var $client \GlobalWeather\SoapStubs\WeatherSoap
 */
 // get the soap client
$client = $factory->getClient('http://www.webservicex.net/weather.asmx?WSDL');

// call the webservice
$result = $client->getWeather(2010, "May", "USA");

// call the webservice with custom headers
$result = $client->getWeather(2010, "May", "USA", Header::asMustUnderstand(new SomeAuth('me', 'pwd')));


```


Please note the `@var $client \GlobalWeather\SoapStubs\WeatherSoap`. The generated metadata have also a "stub" class
that allows modern IDE to give you type hinting for parameters and return data.

This allows you to develop faster your client.

### Using the client with dynamic endpoints

Suppose that you have same Webservice with different endpoints (ex. for each customer), 
so you want to change endpoints dynamically and you don't want to write each new endpoint in your config 
and run the generator for each customer.

With the help of Symfony's `EnvVarProcessorInterface`, 
you can use `alternative_endpoints` to set dynamically the webservice endpoints.

Here is an example:

```yml
# config.yml
soap_client:
  alternative_endpoints:
    MyServiceName:
      MySoapPortName: 'env(custom_vars:ENDPOINT_SERVICE1_PORT1)'
```

So, `SoapClientContainer` will resolve at runtime the endpoint for the specific service and port and the value will be 
taken from the `ENDPOINT_SERVICE1_PORT1` variable.

Example of simple class that implements `EnvVarProcessorInterface`, responsible for providing a values for 
our custom endpoint locations (as `custom_vars:ENDPOINT_SERVICE1_PORT1`).

```php
// SimpleEnvVarProcessor.php used for the `env(custom_vars:*)` variables resolution

use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;

class SimpleEnvVarProcessor implements EnvVarProcessorInterface
{
    private $map = [];

    public function __construct(array $map)
    {
        $this->map = $map;
    }

    public function getEnv($prefix, $name, \Closure $getEnv)
    {
        return $this->map[$name];
    }

    public static function getProvidedTypes()
    {
        return [];
    }
}
```

At the end, to use the `SoapClientContainer`:


```php
// instantiate our variable processor and set the values for our custom variables
$varProcessor = new SimpleEnvVarProcessor([
    'ENDPOINT_SERVICE1_PORT1' => 'http://localhost:8080/service'
]);

// create an empty symfony container and set into it the $varProcessor namined as 'custom_vars'
$varContainer = new \Symfony\Component\DependencyInjection\Container(); 
$varContainer->set('custom_vars', $varProcessor);

// create the soap container and use $varContainer "env()" style variables resolution
$container = new SoapClientContainer();
$container->set('container.env_var_processors_locator', $varContainer);

// now $container can be used as explained in the section "Using the client"
```

In this way the endpoint for the `MyServiceName`.`MySoapPortName` will be dynamically resolved to `http://localhost:8080/service`
even if the WSDL stats something else.

## Note 

The code in this project is provided under the 
[MIT](https://opensource.org/licenses/MIT) license. 
For professional support 
contact [goetas@gmail.com](mailto:goetas@gmail.com) 
or visit [https://www.goetas.com](https://www.goetas.com)
