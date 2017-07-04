<?php

namespace JayceeSoftware\RpcBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use JayceeSoftware\RpcBundle\DependencyInjection\CompilerPass\SchemaCompilerPass;

class JayceeSoftwareRpcBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new SchemaCompilerPass());
    }
}
