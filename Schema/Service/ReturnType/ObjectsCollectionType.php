<?php

namespace JayceeSoftware\RpcBundle\Schema\Service\ReturnType;

class ObjectsCollectionType extends TypeAbstract
{
    protected $type = 'objects_collection';
    /**
     * @var ObjectType
     */
    protected $objectType;
    /**
     * @var TypeInterface[]
     */
    protected $additionalFields = [];

    /**
     * @param ObjectType $objectType
     */
    public function setObjectType(ObjectType $objectType)
    {
        $this->objectType = $objectType;
    }

    /**
     * @return ObjectType
     */
    public function getObjectType()
    {
        return $this->objectType;
    }

    /**
     * @return TypeInterface[]
     */
    public function getAdditionalFields()
    {
        return $this->additionalFields;
    }

    /**
     * @param array $additionalFields
     */
    public function setAdditionalFields($additionalFields)
    {
        $this->additionalFields = $additionalFields;
    }

    public function serialize()
    {
        return serialize(array(
                $this->objectType,
                $this->additionalFields,
                parent::serialize()
            ));
    }

    public function unserialize($data)
    {
        list($this->objectType, $this->additionalFields, $parent) = unserialize($data);
        parent::unserialize($parent);
    }

    public static function __set_state($data)
    {
        if (empty($data['objectType'])) {
            throw new \RuntimeException('Section "objectType" missed.');
        }
        $result = parent::__set_state($data);

        $result->setObjectType($data['objectType']);

        if (!empty($data['additionalFields'])) {
            $result->setAdditionalFields($data['additionalFields']);
        }

        return $result;
    }
}
