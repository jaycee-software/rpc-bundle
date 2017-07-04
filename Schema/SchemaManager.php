<?php

namespace JayceeSoftware\RpcBundle\Schema;

use Symfony\Component\HttpFoundation\Request;
use JayceeSoftware\RpcBundle\Configuration\RpcRequest\Parameter as ParameterConfiguration;

class SchemaManager
{
    /**
     * @var Parameter\TypeInterface[]
     */
    protected $parameterTypes = array();

    public function apply(Request $request, array $configurations)
    {
        $result = [];

        foreach ($configurations as $configuration) {
            if (count($typeResult = $this->applyParameterType($request, $configuration))) {
                $result[$configuration->name] = $typeResult;
            }
        }
        return $result;
    }

    public function applyParameterType(Request $request, ParameterConfiguration $configuration)
    {
        if (!array_key_exists($configuration->type, $this->parameterTypes)) {
            throw new \InvalidArgumentException(sprintf('Parameter type "%s" was not registered.', $configuration->type));
        }
        $type = $this->parameterTypes[$configuration->type];

        if (!$type->supports($configuration)) {
            // @todo we know nothing about configuration
            throw new \RuntimeException(sprintf('Parameter type "%s" does not support configuration "%s".', $configuration->type, $configuration->name));
        }

        return $type->apply($request, $configuration);
    }

    /**
     * Adds a parameter type.
     *
     * @param Parameter\TypeInterface $type
     * @param string                  $name
     */
    public function addParameterType(Parameter\TypeInterface $type, $name)
    {
        if (array_key_exists($name, $this->parameterTypes)) {
            throw new \InvalidArgumentException(sprintf('Parameter type "%s" already added as "%s".', $name, get_class($this->parameterTypes[$name])));
        }
        $this->parameterTypes[$name] = $type;
    }
}
