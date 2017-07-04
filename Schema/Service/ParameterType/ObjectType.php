<?php

namespace JayceeSoftware\RpcBundle\Schema\Service\ParameterType;


class ObjectType extends TypeAbstract
{
    /**
     * @var TypeAbstract[]
     */
    protected $fields = [];

    public function addField(TypeInterface $field)
    {
        $this->fields[$field->getName()] = $field;
    }

    public function getField($name)
    {
        return array_key_exists($name, $this->fields) ? $this->fields[$name] : null;
    }

    public function getFields()
    {
        return $this->fields;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array(
                $this->fields,
                parent::serialize()
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($data)
    {
        list($this->fields, $parent) = unserialize($data);
        parent::unserialize($parent);
    }

    /**
     * @param array $data
     * @return ObjectType
     */
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
