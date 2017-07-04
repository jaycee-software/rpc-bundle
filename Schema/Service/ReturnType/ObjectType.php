<?php

namespace JayceeSoftware\RpcBundle\Schema\Service\ReturnType;

class ObjectType extends TypeAbstract
{
    /**
     * @var TypeInterface[]
     */
    protected $fields = [];

    /**
     * @param TypeInterface $field
     */
    public function addField(TypeInterface $field)
    {
        $this->fields[] = $field;
    }

    /**
     * @return TypeInterface[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    public function serialize()
    {
        return serialize(array(
                $this->fields,
                parent::serialize()
            ));
    }

    public function unserialize($data)
    {
        list($this->fields, $parent) = unserialize($data);
        parent::unserialize($parent);
    }

    public static function __set_state($data)
    {
        $result = parent::__set_state($data);

        if (!empty($data['fields'])) {
            foreach ($data['fields'] as $field) {
                $result->addField($field);
            }
        }

        return $result;
    }
}
