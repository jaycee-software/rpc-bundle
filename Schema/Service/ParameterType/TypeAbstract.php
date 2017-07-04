<?php

namespace JayceeSoftware\RpcBundle\Schema\Service\ParameterType;

abstract class TypeAbstract implements TypeInterface, \Serializable
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var bool
     */
    protected $required = false;
    /**
     * @var mixed
     */
    protected $default;
    /**
     * @var array
     */
    protected $options = [];
    /**
     * @var null|string
     */
    protected $description;

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param boolean $required
     */
    public function setRequired($required)
    {
        $this->required = (bool)$required;
    }

    /**
     * @return boolean
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * @param mixed $default
     */
    public function setDefault($default)
    {
        $this->default = $default;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    public function setOption($option, $value)
    {
        $this->options[$option] = $value;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getOption($name)
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : null;
    }

    /**
     * @return null|string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array(
                $this->name,
                $this->required,
                $this->default,
                $this->options,
                $this->description
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($data)
    {
        list($this->name, $this->required, $this->default, $this->options, $this->description) = unserialize($data);
    }

    /**
     * @param array $data
     * @return TypeAbstract
     */
    public static function __set_state($data)
    {
        if (
            !array_key_exists('name', $data)
        ) {
            throw new \RuntimeException();
        }

        $result = new static();

        $result->setName($data['name']);

        if (!empty($data['required'])) {
            $result->setRequired($data['required']);
        }
        if (!empty($data['default'])) {
            $result->setDefault($data['default']);
        }
        if (!empty($data['options'])) {
            foreach ($data['options'] as $k => $v) {
                $result->setOption($k, $v);
            }
        }
        if (!empty($data['description'])) {
            $result->setDescription($data['description']);
        }

        return $result;
    }
}
