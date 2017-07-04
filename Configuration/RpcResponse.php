<?php

namespace JayceeSoftware\RpcBundle\Configuration;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationAnnotation;

/**
 * Class RpcResponse
 * @package JayceeSoftware\RpcBundle\Configuration
 *
 * @Annotation
 */
class RpcResponse extends ConfigurationAnnotation
{
    /**
     * @var string
     */
    public $type;
    /**
     * @var RpcResponse\Type[]
     */
    public $structure;
    /**
     * @var array
     */
    public $options = [];

    public function getAliasName()
    {
        return 'rpc_response';
    }

    public function allowArray()
    {
        return false;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @param mixed $structure
     */
    public function setStructure($structure)
    {
        $this->structure = $structure;
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        if ('entity' == $this->type && empty($options['class'])) {
            throw new \InvalidArgumentException();
        }
        $this->options = $options;
    }
}
