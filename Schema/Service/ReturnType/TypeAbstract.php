<?php

namespace JayceeSoftware\RpcBundle\Schema\Service\ReturnType;

abstract class TypeAbstract implements TypeInterface, \Serializable
{
    /**
     * @var string
     */
    protected $type;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $serializedName;
    /**
     * @var null|string
     */
    protected $description;

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

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
    public function getSerializedName()
    {
        return $this->serializedName;
    }

    /**
     * @param string $serializedName
     */
    public function setSerializedName($serializedName)
    {
        $this->serializedName = $serializedName;
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
                $this->type,
                $this->name,
                $this->serializedName,
                $this->description
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($data)
    {
        list($this->type, $this->name, $this->serializedName, $this->description) = unserialize($data);
    }

    public static function __set_state($data)
    {
        $result = new static();

        if (!empty($data['name'])) {
            $result->setName($data['name']);
        }
        if (!empty($data['serializedName'])) {
            $result->setSerializedName($data['serializedName']);
        }
        if (!empty($data['description'])) {
            $result->setDescription($data['description']);
        }

        return $result;
    }
}
