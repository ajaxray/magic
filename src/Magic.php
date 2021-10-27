<?php namespace Ajaxray\Magic;


use Ajaxray\Magic\Exception\NotFoundException;
use Psr\Container\ContainerInterface;


/**
 *  Magic - A basic, auto-wiring enabled Dependency Injection Library for PHP8
 *
 * @author Anis Uddin Ahmad <anis.programmer@gmail.com>
 */
class Magic implements ContainerInterface
{
    private array $serviceMap = [];
    private array $parameters = [];

    // Only required if multiple implementation exists
    private array $interfaceMap = [];

    public function map(string $id, string|callable $service, array $options = []) : void
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
    public function param(string $id, mixed $value) : void
    {
        $this->parameters[$id] = $value;
    }

    public function mapInterface(string $interface, string $class, array $options = []) : void
    {
        $this->interfaceMap[$interface] = ['class' => $class, 'options' => $options];
    }

    public function get(string $id)
    {
        if (isset($this->serviceMap[$id])) {
            $params = array_merge($this->serviceMap[$id]['options'], $this->parameters);

            // If the service was set by a callable, call it with container and params
            if (isset($this->serviceMap[$id]['callable'])) {
                return $this->serviceMap[$id]['callable']($this, $params);
            }
            // Otherwise, try to resolve the class
            return $this->resolve($this->serviceMap[$id]['class'], $params);
        } elseif (class_exists($id) || interface_exists($id)) {
            // Try auto-wiring
            return $this->resolve($id, $this->parameters);
        }

        throw new NotFoundException($id);
    }

    public function has(string $id)
    {
        return isset($this->serviceMap[$id]) || class_exists($id);
    }

    private function resolve($class, $parameters = [])
    {
        $classReflection = new \ReflectionClass($class);

        if ($classReflection->isInterface()) {
            // Replace with ReflectionClass of a class that implements the interface
            list($classReflection, $options) = $this->findImplementation($classReflection->getName());
            $parameters = array_merge($options, $parameters);
        } else if (!$classReflection->isInstantiable()) {
            throw new \RuntimeException("$class is not instantiable");
        }

        $constructor = $classReflection->getConstructor();
        $constructorParams = $constructor? $constructor->getParameters() : [];
        $dependencies = [];

        foreach ($constructorParams as $constructorParam) {

            $type = $constructorParam->getType();

            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {

                $paramInstance = $this->resolve($type->getName(), $parameters);
                array_push($dependencies, $paramInstance);

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

        return $classReflection->newInstance(...$dependencies);
    }

    private function findImplementation($iName) :array
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
     * @param string $interfaceName
     * @return array
     */
    private function whoImplements(string $interfaceName) :array
    {
        return array_filter(
            get_declared_classes(),
            fn ($c) => in_array($interfaceName, class_implements($c))
        );
    }
}