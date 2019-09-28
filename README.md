Service HealthCheck
===================

With  microservice architectures, these days it is important that one
knows the health of service which in turn checks the health of its depending
services.

This library allows one to check the health status of a PHP microservice, enabling
it to check its depending services like APIs, databases, and caches, with 
an easy-to-use interface.

## Usage

### Basics
```php
use Giffgaff\ServiceHealthCheck\Interfaces\HealthCheckInterface;
use Psr\Log\LoggerInterface;

$servicesToCheck = [
    'service1' => <HelatchCheckInterface object1>,
    'service2' => <HelatchCheckInterface object2>
    ...
];

$logger = <LoggerInterface object>;

$healthCheck = new ServiceHealthCheck($servicesToCheck, $logger);
```

### Http checks
[Refer code](./src/HttpClientHealthCheck.php)
```php
use Giffgaff\ServiceHealthCheck\HttpClientHealthCheck;
use Giffgaff\ServiceHealthCheck\ServiceHealthCheck;
use GuzzleHttp\Psr7\Request;

$debugMode = true;
$requestOptions = ['verify' => false]; // Add any more HTTP headers, ex: auth
$method = 'GET';
$url = 'https://www.google.com';
$serviceName = 'a-third-party/google';

$httpCheck = new HttpClientHealthCheck($serviceName, $debugMode);
$httpCheck->setRequest(new Request($method, $url, $requestOptions));

$servicesToCheck = [
    $serviceName => $httpCheck
];

/*
@var HealthCheckResponse
*/
$healthCheck = new ServiceHealthCheck($servicesToCheck, $logger);
```

### Redis checks
[Refer code](./src/RedisHealthCheck.php)

This tests a simple read and write operation into the redis client specified.
The client uses the Predis\Client interface.

```php
use Predis\Client;
$redisCheck = new RedisHealthCheck($serviceName, $debugMode);

$redisClient = new Client(/**/);
$redisCheck.setClient($redisClient);

/*
@var HealthCheckResponse
*/
$healthCheck = new ServiceHealthCheck(['redis-svc' => $redisCheck], $logger);
```

### Memcached checks
[Refer code](./src/RedisHealthCheck.php)

This tests a simple read and write operation into the memcached client specified.
The client uses the Memcached interface.

```php
use Predis\Client;
$memcachedCheck = new MemcachedHealthCheck($serviceName, $debugMode);

$memcachedClient = new Client(/**/);
$memcachedCheck.setClient($memcachedClient);

/*
@var HealthCheckResponse
*/
$healthCheck = new ServiceHealthCheck(['memcached-svc' => $memcachedCheck], $logger);
```

## TODO
* Add a database health check
* Add README for the more common CacheHealthCheck. It replaces RedisHealthCheck and MemcachedHealthCheck 
* Can add any number of healthcheck objects. Possibilities are endless. Imagine GRPC endpoints, ElasticSearch,
S3 connectivity, file system.

Contributions welcome!

## License
MIT

## Authors
* Premkumar Anand
* Ian Harry
