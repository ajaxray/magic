Magic - tiny Dependency Injection Container for PHP 8
=========================

A tiny Dependency Injection Container for PHP 8 under 200 lines.
Made for fun and exploring PHP Reflection features. 
But it does what it claims. 

Features
--------

* Compatible with [PSR-11: Container interface](https://www.php-fig.org/psr/psr-11/)
* Made for PHP8
* Service binding by Class, Interface or anonymous functions
* Resolve dependencies using:
  - Auto-wiring by class/interface name
  - Mapped Name/identifier of a service
  - Interface of the service
  - Implementation of the service
* Constructor DI capabilities based on Type-hinting
* Life-circle control of the objects (singleton / new instance per request)
* Easy to use with any framework (that usages PSR-11 compatible container) or even a plain php file
* [PSR-4 autoloading](https://www.php-fig.org/psr/psr-4/) compliant structure

_Caution: This library is still going through initial development phase!_

## Installation

Just pull it in your project using composer.
```shell
composer require ajaxray/magic --with-all-dependencies
```
Or even you can [download](https://github.com/ajaxray/magic/archive/refs/heads/main.zip) it and include manually.  

## How to use

### Basics 

First, make an instance of `Magic` and bind service class.
```php
$magic = new Magic();
$magic->map('logger', MyLogger::class);
```

Now you can get instance of `MyLogger` using the service name `logger`.
```php
$logger = $magic->get('logger');
$logger->info('Using Magic as dependency injection container');
```
If `MyLogger` constructor expects some arguments, `Magic` will try to instantiate and supply them too. 
See next section for more detail on arguments.

### Resolving service arguments
A service constructor may require some arguments to instantiate it. 
Container will try to supply them with different strategy based on argument type. 

#### Object arguments
Type hint will be used to determine the type of object.
Magic will try to instantiate the object arguments based on -
- If the name of parameter matches any defined service identifier. Type of object will be checked for safety.  
- If any service defined with the Class/Interface name. Interface should be mapped to a concrete class in that case. 
- Auto-wiring. Scalar parameters should be resolvable from container-wide defined parameters. 

For example, let's think we have this constructor in a class -  
```php
public function __construct(\Doctrine\DBAL\Connection $dbConn)   
```
The following definitions will be tried sequentially.  
Based on name matching -
```php
$container->map('dbConn', function($m, $params) {
    return \Doctrine\DBAL\DriverManager::getConnection(['url' => $params['dsn']]);
});
```
Based on Type matching - 
```php
$container->map(Connection::class, function($m, $params) {
    return \Doctrine\DBAL\DriverManager::getConnection(['url' => $params['dsn']]);
});
```
And finally, auto-wiring will be tried if none of the above definitions found. 

#### Scalar arguments
You have to set the scalar arguments manually. 
Parameters can be set container-wide or during service definition.

Container-wide set parameters will be used for all service with the same argument name.
```php
// e,g, new MyDbConnection($user, $password, $host = 'localhost', $port = 3306);
$magic->map('db', MyDbConnection::class);

$magic->param('host', 'theHostNameOrIP');
$magic->param('user', 'root');
$magic->param('password', 'TheSecret');

// parameters will be supplied by name matching automatically
$magic->get('db');  
```

Service specific argument values can be supplied at the time of service binding. 
These params will be used with ONLY this specific service. 
```php
$magic->map('db', MyDbConnection::class, [
    'host' => 'theHostNameOrIP',
    'user' => 'root',
    'pass' => 'TheSecret',
]);
```

_Hint: Arguments can be specified from `.env` file from the coming release._ 

### Auto-wiring 

In most of the cases, services can be loaded without binding anything if its dependencies (constructor params, if any) 
satisfies the following criteria:
- Scalar dependencies are resolvable from globally set parameters.
- Object/Interface dependencies are type hinted and auto-loadable, 
- Object/Interface dependencies (and their dependencies) satisfies these prerequisites of Auto-wiring or explicitly mapped

```php
$magic = new Magic();
$magic->get(MyDbConnection::class);
```

### Interface Binding

You can bind an interface as a service. 
In this case, you have to map the interface with an implementation to be instantiated.
```php
$magic->map('notifier', NotifierInterface::class, ['receiver' => 'receiver@xyz.tld']);
$magic->mapInterface(NotifierInterface::class, MailNotification::class);

$magic->get('notifier')->notify('The message to send');
```

### Binding using anonymous function 
You can bind service with Pimple/Laravel style anonymous functions. 
The function will receive an instance of container and parameters array.
```php
// Simple
$magic->map('greeter', fn($m, $params) => new Greeter($params['name']), ['name' => 'ajaxray']);

// Complex
$magic->param('user', 'sysadmin');
$magic->param('pass', 'TheSecret');

$magic->map('mailer', function ($m, $params) {
        $transport = (new Swift_SmtpTransport($params['smtp.host'], 25))
            ->setUsername($params['user'])
            ->setPassword($params['pass'])
        ;

        return new Swift_Mailer($transport);        
    }, ['smtp.host' => 'smtp.example.tld']);
```
In the above example, `user` and `pass` will be resolved from globally set params. 
That means, the globally set params will be merged with the service specific params while resolving or passing to service binding functions.

### Service life cycle (singleton or factory)

By default, if a service is instantiated once, it will be reused for subsequent `get()` calls or resolving other constructor parameters.
But you can disable this behaviour by passing `@cacheable` parameter.
```php
$magic->map('dbMapper', ActiveRecord::class, ['@cacheable' => false]);

// dbMapper will not be cached and will return new instance for every get() call
$aUser = $magic->get('dbMapper')->load('User', 3);
$otherUser = $magic->get('dbMapper')->load('User', 26);
```

## Testdox
```text
PHPUnit 9.5.10 by Sebastian Bergmann and contributors.

Auto Wiring (Ajaxray\Test\AutoWiring)
 ✔ Resolve class by name without constructor
 ✔ Resolve class by name with scalar param constructor
 ✔ Resolve class by name with object param constructor

Basic Class (Ajaxray\Test\BasicClass)
 ✔ Service mapping without constructor
 ✔ Service mapping with scalar param constructor
 ✔ Service mapping with object param constructor

Object Chaining (Ajaxray\Test\ObjectChaining)
 ✔ Resolve classes in chained object graph

Object Lifecycle (Ajaxray\Test\ObjectLifecycle)
 ✔ Provides same instance for multiple get call by default
 ✔ Provides same instance for multiple get call of interface
 ✔ Provides same instance for multiple get call of callback binding
 ✔ Service caching can be disabled for class mapping
 ✔ Service caching can be disabled for interface
 ✔ Service caching can be disabled for callback binding

Resolve Interface (Ajaxray\Test\ResolveInterface)
 ✔ Service loading by interface if single implementation
 ✔ Resolve interface type hint to implementation if single implementation
 ✔ Service loading by mapped interface
 ✔ Resolve mapped interface type hint to implementation

Service Binding By Callable (Ajaxray\Test\ServiceBindingByCallable)
 ✔ Service mapping without constructor
 ✔ Service mapping with scalar param constructor
 ✔ Service mapping with object param constructor
 ✔ Callable can serve non object types

Time: 00:00.015, Memory: 6.00 MB

OK (21 tests, 23 assertions)
```