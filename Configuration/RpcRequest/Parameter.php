<?php

namespace JayceeSoftware\RpcBundle\Configuration\RpcRequest;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationAnnotation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator;

/**
 * Class Param
 * @package JayceeSoftware\RpcBundle\Configuration
 * @subpackage RpcRequest
 *
 * @Annotation
 */
class Parameter extends ConfigurationAnnotation
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
     * @var array
     */
    public $options = array();
    /**
     * @var string
     * @todo move it to $options
     */
    public $condition;
    /**
     * @var mixed
     */
    public $default = null;
    /**
     * @var bool
     */
    public $required = true;
    /**
     * Parameter description. Used for export to public doc.
     * Optional.
     *
     * @var string
     */
    public $description = '';

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
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @param string $condition
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;
    }

    /**
     * @param mixed $default
     */
    public function setDefault($default)
    {
        $this->default = $default;
    }

    /**
     * @param boolean $required
     */
    public function setRequired($required)
    {
        $this->required = (boolean) $required;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getAliasName()
    {
        return 'rpc_request_parameters';
    }

    public function allowArray()
    {
        return true;
    }

    public function getData(Request $request)
    {
        return $request->get($this->name, $this->default);
    }

}
