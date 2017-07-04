<?php

namespace JayceeSoftware\RpcBundle\Schema\Service\ParameterType;

class ScalarType extends TypeAbstract
{
    /**
     * @var string
     */
    protected $type = 'string';

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array(
                $this->type,
                parent::serialize()
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($data)
    {
        list($this->type, $parent) = unserialize($data);
        parent::unserialize($parent);
    }

    /**
     * @param array $data
     * @return ScalarType
     */
    public static function __set_state($data)
    {
        $result = parent::__set_state($data);

        if (!empty($data['type'])) {
            $result->setType($data['type']);
        }

        return $result;
    }
}
