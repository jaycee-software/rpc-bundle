<?php

namespace JayceeSoftware\RpcBundle\Schema\Parameter;

use Doctrine\Common\Persistence\ManagerRegistry;
use JayceeSoftware\RpcBundle\Configuration\RpcRequest\Parameter as ParameterConfiguration;
use Symfony\Component\HttpFoundation\Request;

class TypeEntity extends TypeObject
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry = null)
    {
        $this->registry = $registry;
    }

    public function apply(Request $request, ParameterConfiguration $configuration)
    {
        throw new \RuntimeException('Not implemented yet');
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParameterConfiguration $configuration)
    {
        // if there is no manager, this means that only Doctrine DBAL is configured
        if (null === $this->registry || !count($this->registry->getManagers())) {
            return false;
        }

        $options = self::getOptions($configuration);

        if (null === $options['class']) {
            return false;
        }

        // Doctrine Entity?
        $em = $this->getManager($options['entity_manager'], $options['class']);
        if (null === $em) {
            return false;
        }

        return ! $em->getMetadataFactory()->isTransient($options['class']);
    }

    public static function getOptions(ParameterConfiguration $configuration)
    {
        return array_replace(array(
            'class'          => null,
            'entity_manager' => null,
            'exclude'        => array(), // ?
            'mapping'        => array(), // ?
            'strip_null'     => false,   // ?
        ), $configuration->options);
    }

    private function getManager($name, $class)
    {
        if (null === $name) {
            return $this->registry->getManagerForClass($class);
        }

        return $this->registry->getManager($name);
    }
}
