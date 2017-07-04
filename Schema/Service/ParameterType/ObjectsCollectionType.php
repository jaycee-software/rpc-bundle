<?php

namespace JayceeSoftware\RpcBundle\Schema\Service\ParameterType;

class ObjectsCollectionType extends TypeAbstract
{
    /**
     * @var ObjectType
     */
    protected $objectType;

    /**
     * @return ObjectType
     */
    public function getObjectType()
    {
        return $this->objectType;
    }

    /**
     * @param ObjectType $objectType
     */
    public function setObjectType($objectType)
    {
        $this->objectType = $objectType;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array(
                $this->objectType,
                parent::serialize()
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($data)
    {
        list($this->objectType, $parent) = unserialize($data);
        parent::unserialize($parent);
    }

    /**
     * @param array $data
     * @return ObjectsCollectionType
     */
    public static function __set_state($data)
    {
        if (!array_key_exists('objectType', $data)) {
            throw new \RuntimeException();
        }

        $result = parent::__set_state($data);
        $result->setObjectType($data['objectType']);

        return $result;
    }
}
