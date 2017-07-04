<?php

namespace JayceeSoftware\RpcBundle\Schema;

class Schema implements \Serializable
{
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
     * @var Service\ParameterType\TypeInterface[]
     */
    protected $parameters = [];
    /**
     * @var Service\Service[]
     */
    protected $services = [];

    /**
     * @return string
     */
    public function getTransport()
    {
        return $this->transport;
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
    public function getEnvelope()
    {
        return $this->envelope;
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
    public function getContentType()
    {
        return $this->contentType;
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
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param string $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * @return Service\ParameterType\TypeInterface[]
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param Service\ParameterType\TypeInterface[] $parameters
     */
    public function setParameters($parameters)
    {
        $this->parameters = [];

        foreach ($parameters as $parameter) {
            $this->addParameter($parameter);
        }
    }

    public function addParameter(Service\ParameterType\TypeInterface $parameter)
    {
        $this->parameters[] = $parameter;
    }

    /**
     * @param Service\Service $service
     */
    public function addService(Service\Service $service)
    {
        $this->services[$service->getName()] = $service;
    }

    /**
     * @param Schema $schema
     * @param string $prefix
     */
    public function addServicesFromSchema(Schema $schema, $prefix)
    {
        $schemaParameters = $schema->getParameters();

        foreach ($schema->getServices() as $service) {
            $toAdd = clone $service;
            $toAdd->setName($prefix.$toAdd->getName());

            foreach ($schemaParameters as $schemaParameter) {
                if (!$toAdd->hasParameter($schemaParameter->getName())) {
                    $toAdd->addParameter($schemaParameter);
                }
            }
            $this->addService($toAdd);
        }
    }

    /**
     * @return Service\Service[]
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array(
            $this->services
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list($this->services) = unserialize($serialized);
    }

    public static function __set_state($data)
    {
        $result = new static();

        if (!empty($data['services'])) {
            foreach ($data['services'] as $service) {
                $result->addService($service);
            }
        }

        return $result;
    }
}
