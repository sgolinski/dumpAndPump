<?php

namespace Application;

use Application\Exceptions\ContainerException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;


class Container implements ContainerInterface
{
    private array $entries = [];

    public function get(string $id)
    {
        if ($this->has($id)) {
            $entry = $this->entries[$id];

            return $entry($this);
        }
        $this->resolve($id);
    }

    public function has(string $id): bool
    {
        return isset($this->entries[$id]);
    }

    public function set(string $id, callable $concrete)
    {
        $this->entries[$id] = $concrete;
    }

    public function resolve(string $id)
    {
        $reflectionClass = new ReflectionClass($id);

        if (!$reflectionClass->isInstantiable()) {
            throw new ContainerException('Class ' . $id . " is not instantiable");
        }

        $constructor = $reflectionClass->getConstructor();

        if (!$constructor) {
            return new $id;
        }

        $parameters = $constructor->getParameters();
        if (!$parameters) {
            return new $id;
        }

        $dependencies = array_map(function (ReflectionParameter $parameter) {
            $name = $parameter->getName();
            $type = $parameter->getType();

            if (!$type) {
                throw new ContainerException('Failed to resolve class');
            }

            if ($type instanceof ReflectionUnionType) {
                throw new ContainerException('Failed to resolve class');
                //handling uniontypes
//                $types = $type->getTypes();
//                array_map(function (ReflectionUnionType $unionType){
//                    $name = $unionType->getName();
//                }, $types);
            }

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                return $type->getName();
            }
            throw new ContainerException('Failed to resolve class');
        }, $parameters);

        return $reflectionClass->newInstanceArgs($dependencies);
    }
}