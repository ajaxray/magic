Magic - PSR 11 compliant tiny Dependency Injection Container
=========================

Made for fun and exploring PHP Reflection features. 

Features
--------

* PSR-4 autoloading compliant structure
* Compatible with [PSR-11: Container interface](https://www.php-fig.org/psr/psr-11/)
* Made for PHP7.4 / PHP8
* Resolve dependencies using:
  - Auto-wiring by class/interface name
  - Mapped Name/identifier of a service
  - Interface of the service
  - Implementation of the service
  - Support constructor DI capabilities based on type-hinting
  - Manage the life-circle of the objects (singleton/per request etc)
* Easy to use to any framework or even a plain php file

### Testdox
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