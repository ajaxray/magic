<?php
declare(strict_types=1);

namespace Ajaxray\Magic;

use Ajaxray\Magic\Exception\NotFoundException;
use Psr\Container\ContainerInterface;


/**
 *  Magic - A tiny auto-wiring enabled Dependency Injection Container for PHP8
 *
 * @author Anis Uddin Ahmad <anis.programmer@gmail.com>
 */
class Magic implements ContainerInterface
{
    private array $serviceMap = [];

    // Static Parameters and service configuration options
    private array $parameters = [
        '@cacheable' => true
    ];
    private array $serviceCache = [];  // Repository of instantiated objects
    private array $interfaceMap = [];  // Only required if multiple implementation exists


    public function map(string $id, string|callable $service, array $options = []): void
    {
        if (is_callable($service)) {
            $this->serviceMap[$id] = ['callable' => $service, 'options' => $options];
        } else {
            $this->serviceMap[$id] = ['class' => $service, 'options' => $options];
        }
    }

    /**
     * Set scalar parameters as key-value
     *
     * @param string $id
     * @param mixed $value
     */
    public function param(string $id, mixed $value): void
    {
        $this->parameters[$id] = $value;
    }

    /**
     * Map an interface to an implementation class
     *
     * @param string $interface
     * @param string $class
     * @param array $options
     */
    public function mapInterface(string $interface, string $class, array $options = []): void
    {
        $this->interfaceMap[$interface] = ['class' => $class, 'options' => $options];
    }

    /**
     * @inheritDoc
     */
    public function get(string $id)
    {
        if (isset($this->serviceCache[$id])) {
            return $this->serviceCache[$id];
        }

        $service = null;
        $params = $this->parameters;

        if (isset($this->serviceMap[$id])) {
            $params = array_merge($this->parameters, $this->serviceMap[$id]['options']);

            // If the service was set by a callable, call it with container and params
            if (isset($this->serviceMap[$id]['callable'])) {
                $service = $this->serviceMap[$id]['callable']($this, $params);
            } else {
                // Otherwise, try to resolve the class
                $service = $this->resolve($this->serviceMap[$id]['class'], $params);
            }
        } elseif (class_exists($id) || interface_exists($id)) {
            // Try auto-wiring
            $service = $this->resolve($id, $params);
        }

        if (!is_null($service)) {
            return $params['@cacheable'] === false ? $service : $this->serviceCache[$id] = $service;
        }

        throw new NotFoundException($id);
    }

    /**
     * @inheritDoc
     */
    public function has(string $id)
    {
        return isset($this->serviceMap[$id]) || class_exists($id);
    }

    /**
     * Instantiate a class with providing dependencies (by resolving constructor arguments)
     *
     * @param string $class Name of the Class to instantiate
     * @param array $parameters
     * @return object
     * @throws \ReflectionException
     */
    private function resolve(string $class, array $parameters = []): object
    {
        $classReflection = new \ReflectionClass($class);

        if ($classReflection->isInterface()) {
            // Replace with ReflectionClass of a class that implements the interface
            list($classReflection, $options) = $this->findImplementation($classReflection->getName());
            $parameters = array_merge($options, $parameters);
        } else if (!$classReflection->isInstantiable()) {
            throw new \RuntimeException("$class is not instantiable");
        }

        $dependencies = $this->getDependencies($classReflection, $parameters, $class);

        return $classReflection->newInstance(...$dependencies);
    }

    /**
     * Find concrete Class of an interface
     *
     * @throws \ReflectionException
     */
    private function findImplementation(string $iName): array
    {
        // Check if any class was mapped for the interface
        if (isset($this->interfaceMap[$iName])) {
            return [
                new \ReflectionClass($this->interfaceMap[$iName]['class']),
                $this->interfaceMap[$iName]['options']
            ];
        } else {
            // Otherwise, if a single class implements it, use the class
            $implementers = $this->whoImplements($iName);
            if (count($implementers) > 1) {
                throw new \LogicException("Multiple implementation found for $iName. Please specify the target class using Magic::mapInterface()");
            } else {
                return [new \ReflectionClass(current($implementers)), []];
            }
        }
    }

    /**
     * Get list of classes that implements an Interface
     */
    private function whoImplements(string $interfaceName): array
    {
        return array_filter(
            get_declared_classes(),
            fn ($c) => in_array($interfaceName, class_implements($c))
        );
    }

    /**
     * Prepare constructor arguments of a class
     *
     * @throws \ReflectionException
     */
    private function getDependencies(mixed $classReflection, mixed $parameters, string $class): array
    {
        $constructor = $classReflection->getConstructor();
        $constructorParams = $constructor ? $constructor->getParameters() : [];
        $dependencies = [];

        /** @var \ReflectionParameter $constructorParam */
        foreach ($constructorParams as $constructorParam) {

            $type = $constructorParam->getType();
            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                array_push($dependencies, $this->resolveDependency($constructorParam));

            } else {
                $name = $constructorParam->getName(); // get the name of param

                // check this param value exist in $parameters
                if (array_key_exists($name, $parameters)) { // if exist
                    array_push($dependencies, $parameters[$name]);

                } else {
                    // param not found. Throw unless optional
                    if (!$constructorParam->isOptional()) {
                        throw new \RuntimeException("Can not resolve parameter: '$name' of class: '$class'");
                    }
                }
            }
        }

        return $dependencies;
    }

    /**
     * Resolve Constructor parameters
     *
     * @param \ReflectionParameter $constructorParam
     * @return mixed
     */
    private function resolveDependency(\ReflectionParameter $constructorParam): mixed
    {
        $typeName = $constructorParam->getType()->getName();

        // First check if any service defined with parameter name
        if ($this->has($constructorParam->name)) {
            $instance = $this->get($constructorParam->name);

            // Confirm instance type
            if ($instance instanceof $typeName) {
                return $instance;
            }
        }

        // Otherwise, resolve from Class name
        return $this->get($typeName);
    }
}