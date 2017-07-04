<?php

namespace JayceeSoftware\RpcBundle\Schema\Parameter;

use JayceeSoftware\RpcBundle\Configuration\RpcRequest\Parameter as ParameterConfiguration;
use Symfony\Component\HttpFoundation\Request;

interface TypeInterface
{
    /**
     * @param \JayceeSoftware\RpcBundle\Configuration\RpcRequest\Parameter $configuration
     * @return bool
     */
    public function supports(ParameterConfiguration $configuration);

    /**
     * @param Request $request
     * @param \JayceeSoftware\RpcBundle\Configuration\RpcRequest\Parameter $configuration
     * @return array
     */
    public function apply(Request $request, ParameterConfiguration $configuration);
}
