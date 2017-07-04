<?php

namespace JayceeSoftware\RpcBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class SchemaCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $this->processManager($container);
    }

    private function processManager(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('fxs_rpc.schema.manager')) {
            return;
        }

        $definition = $container->getDefinition('fxs_rpc.schema.manager');

        $taggedServices = $container->findTaggedServiceIds('fxs_rpc.rpc_request.parameter_type');

        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $definition->addMethodCall(
                    'addParameterType',
                    array(
                        new Reference($id),
                        $attributes['type'],
                    )
                );
            }
        }
    }
}
