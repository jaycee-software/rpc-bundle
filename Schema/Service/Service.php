<?php

namespace JayceeSoftware\RpcBundle\Schema\Service;

class Service implements \Serializable
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $transport;
    /**
     * @var string
     */
    protected $envelope;
    /**
     * @var string
     */
    protected $contentType;
    /**
     * @var string
     */
    protected $target;
    /**
     * @var ParameterType\TypeInterface[]
     */
    protected $parameters;
    /**
     * @var null|ReturnType\TypeAbstract[]
     */
    protected $returns;
    /**
     * @var null|string
     */
    protected $description;

    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $transport
     */
    public function setTransport($transport)
    {
        $this->transport = $transport;
    }

    /**
     * @return string
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * @param string $envelope
     */
    public function setEnvelope($envelope)
    {
        $this->envelope = $envelope;
    }

    /**
     * @return string
     */
    public function getEnvelope()
    {
        return $this->envelope;
    }

    /**
     * @param string $contentType
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @param string $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param ParameterType\TypeInterface[] $parameters
     */
    public function setParameters($parameters)
    {
        $this->parameters = [];

        foreach ($parameters as $parameter) {
            $this->addParameter($parameter);
        }
    }

    public function addParameter(ParameterType\TypeInterface $parameter)
    {
        $this->parameters[$parameter->getName()] = $parameter;
    }

    /**
     * @return ParameterType\TypeInterface[]
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    public function hasParameter($name)
    {
        return array_key_exists($name, $this->parameters);
    }

    /**
     * @return ReturnType\TypeAbstract[]|null
     */
    public function getReturns()
    {
        return $this->returns;
    }

    /**
     * @param ReturnType\TypeAbstract[]|null $returns
     */
    public function setReturns($returns)
    {
        $this->returns = [];

        foreach ($returns as $return) {
            $this->addReturn($return);
        }
    }

    /**
     * @param ReturnType\TypeAbstract $return
     */
    public function addReturn(ReturnType\TypeAbstract $return)
    {
        $this->returns[] = $return;
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

    public function serialize()
    {
        return serialize(array(
                $this->name,
                $this->transport,
                $this->envelope,
                $this->contentType,
                $this->target,
                $this->parameters,
                $this->returns,
                $this->description
            ));
    }

    public function unserialize($data)
    {
        list($this->name, $this->transport, $this->envelope, $this->contentType, $this->target, $this->parameters, $this->returns, $this->description) = unserialize($data);
    }

    public static function __set_state($data)
    {
        if (
            !array_key_exists('name', $data)
        ) {
            throw new \RuntimeException();
        }
        $result = new static($data['name']);

        if (!empty($data['transport'])) {
            $result->setTransport($data['transport']);
        }
        if (!empty($data['envelope'])) {
            $result->setEnvelope($data['envelope']);
        }
        if (!empty($data['contentType'])) {
            $result->setContentType($data['contentType']);
        }
        if (!empty($data['target'])) {
            $result->setTarget($data['target']);
        }
        if (!empty($data['parameters'])) {
            $result->setParameters($data['parameters']);
        }
        if (!empty($data['returns'])) {
            $result->setReturns($data['returns']);
        }
        if (!empty($data['description'])) {
            $result->setDescription($data['description']);
        }

        return $result;
    }
}
