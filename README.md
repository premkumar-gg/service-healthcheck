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
use GuzzleHttp\Psr7\Response;

$servicesToCheck = [
    'service1' => <HelatchCheckInterface object1>,
    'service2' => <HelatchCheckInterface object2>
    ...
];

$logger = <LoggerInterface object>;

$healthCheck = new ServiceHealthCheck($servicesToCheck, $logger);

/*
@var GuzzleHttp\Psr7\Response
*/
$response = $healthCheck->getServiceStatuses();
```

The response status will be the worst case of the responses from all the
services provided. The response data will be the responses from each
individual service.

```
[
    status => <worst_case_status_code>
    data => [
        "service1" => [
            status => <status_code_of_service1>
            data => <response_data_of_service1>
        ],
        "service2" => [
            status => <status_code_of_service2>
            data => <response_data_of_service2>
        ],
        ...
    ]
]
```

### Http checks
[Refer code](./src/HttpClientHealthCheck.php)
```php
use Giffgaff\ServiceHealthCheck\HttpClientHealthCheck;
use Giffgaff\ServiceHealthCheck\ServiceHealthCheck;
use GuzzleHttp\Psr7\Request;

$requestOptions = ['verify' => false]; // Add any more HTTP headers, ex: auth
$method = 'GET';
$url = 'https://www.google.com';
$serviceName = 'a-third-party/google';

$httpCheck = new HttpClientHealthCheck($serviceName);
$httpCheck->setRequest(new Request($method, $url, $requestOptions));

$servicesToCheck = [
    $serviceName => $httpCheck
];

$healthCheck = new ServiceHealthCheck($servicesToCheck, $logger);
$healthCheck->getServiceStatuses();
$response = $healthCheck->getServiceStatuses();
```

### Redis checks
[Refer code](./src/RedisHealthCheck.php)

This tests a simple read and write operation into the redis client specified.
The client uses the Predis\Client interface.

```php
use Predis\Client;
$redisCheck = new RedisHealthCheck($serviceName);

$redisClient = new Client(/**/);
$redisCheck.setClient($redisClient);

$healthCheck = new ServiceHealthCheck(['redis-svc' => $redisCheck], $logger);
$response = $healthCheck->getServiceStatuses();
```

### Memcached checks
[Refer code](./src/RedisHealthCheck.php)

This tests a simple read and write operation into the memcached client specified.
The client uses the Memcached interface.

```php
use Predis\Client;
$memcachedCheck = new MemcachedHealthCheck($serviceName);

$memcachedClient = new Client(/**/);
$memcachedCheck.setClient($memcachedClient);

$healthCheck = new ServiceHealthCheck(['memcached-svc' => $memcachedCheck], $logger);
$response = $healthCheck->getServiceStatuses();
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
