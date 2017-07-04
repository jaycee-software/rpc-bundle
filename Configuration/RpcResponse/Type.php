<?php

namespace JayceeSoftware\RpcBundle\Configuration\RpcResponse;

/**
 * Class Type
 * @package JayceeSoftware\RpcBundle
 * @subpackage Configuration\RpcResponse
 *
 * @Annotation
 */
class Type
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $type;
    /**
     * @var null|string
     */
    public $description;

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @param null|string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }
}
