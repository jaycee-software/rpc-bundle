<?php

namespace JayceeSoftware\RpcBundle\Schema\Formatter;

use JayceeSoftware\RpcBundle\Schema\Schema;
use JayceeSoftware\RpcBundle\Schema\Service\Service;
use JayceeSoftware\RpcBundle\Schema\Service\ParameterType;
use JayceeSoftware\RpcBundle\Schema\Service\ReturnType;

class SMD implements FormatterInterface
{
    /**
     * {@inheritdoc}
     */
    public function format(Schema $schema)
    {
        $result = [
            'transport' => $schema->getTransport() ?: 'POST',
            'envelope' => $schema->getEnvelope() ?: 'JSON-RPC-2.0',
            'contentType' => $schema->getContentType() ?: 'application/json',
            'target' => $schema->getTarget() ?: '',
            'additionalParameters' => false
        ];

        if (!empty($parameters = $schema->getParameters())) {
            $result['parameters'] = [];

            foreach ($parameters as $p) {
                $result['parameters'][] = $this->processParameter($p);
            }
        }

        $result['services'] = [];

        foreach ($schema->getServices() as $service) {
            $formattedService = [
                'additionalParameters' => false
            ];

            if (!empty($transport = $service->getTransport())) {
                $formattedService['transport'] = $transport;
            }
            if (!empty($envelope = $service->getEnvelope())) {
                $formattedService['envelope'] = $envelope;
            }
            if (!empty($contentType = $service->getContentType())) {
                $formattedService['contentType'] = $contentType;
            }
            if (!empty($target = $service->getTarget())) {
                $formattedService['target'] = $target;
            }
            if (!empty($parameters = $service->getParameters())) {
                $formattedService['parameters'] = [];

                foreach ($parameters as $p) {
                    $formattedService['parameters'][] = $this->processParameter($p);
                }
            }
            if (!empty($returns = $service->getReturns())) {
                foreach ($returns as $return) {
                    $formattedService['returns'] = ['type' => $this->processReturn($return)];
                }
            }

            $result['services'][$service->getName()] = $formattedService;
        }

        return json_encode($result);
    }

    /**
     * {@inheritdoc}
     */
    public function deserialize($data)
    {
        if (is_string($data)) {
            $data = json_decode($data);
        }
        $schema = new Schema();
        $schema->setTransport($data['transport']);
        $schema->setEnvelope($data['envelope']);
        $schema->setContentType($data['contentType']);
        $schema->setTarget($data['target']);

        if (!empty($data['parameters'])) {
            foreach ($data['parameters'] as $parameter) {
                $schema->addParameter($this->deserializeParameter($parameter));
            }
        }
        foreach ($data['services'] as $name => $service) {
            $schema->addService($this->deserializeService($name, $service));
        }

        return $schema;
    }

    private function processParameter(ParameterType\TypeInterface $parameter)
    {
        $result = ['name' => $this->translateName($parameter->getName())];

        switch (true) {
            case $parameter instanceof ParameterType\ScalarType:
                $result['type'] = is_array($type = $parameter->getType()) ? join('|', $type) : $type;

                if (null !== $default = $parameter->getDefault()) {
                    $result['default'] = $default;
                }
                if (false === $parameter->getRequired()) {
                    $result['optional'] = true;
                }
                if (null !== $min = $parameter->getOption('min')) {
                    $result['min'] = $min;
                }
                if (null !== $max = $parameter->getOption('max')) {
                    $result['max'] = $max;
                }
                if (null !== $description = $parameter->getDescription()) {
                    $result['description'] = $description;
                }
                break;
            case $parameter instanceof ParameterType\ObjectType:
                if (false !== $parameter->getRequired()) {
                    $result['optional'] = true;
                }

                $result['type'] = [];

                foreach ($parameter->getFields() as $k => $f) {
                    $result['type'][] = $this->processParameter($f);
                }
        }

        return $result;
    }

    private function processReturn(ReturnType\TypeInterface $return)
    {
        $result = [];

        switch (true) {
            case $return instanceof ReturnType\ObjectType:
                foreach ($return->getFields() as $field) {
                    switch (true) {
                        case $field instanceof ReturnType\ObjectType:
                            $result[$field->getSerializedName() ?: $this->translateName($field->getName())] = $this->processReturn($field);
                            break;
                        case $field instanceof ReturnType\TypeInterface:
                            $result[$field->getSerializedName() ?: $this->translateName($field->getName())] = $this->processReturn($field);
                    }
                }
                break;
            case $return instanceof ReturnType\ListOfObjectsType:
                $result['type'] = 'list_of_objects';
                $result['object'] = $this->processReturn($return->getObjectType());

                foreach ($return->getAdditionalFields() as $af) {
                    $result[$af->getSerializedName() ?: $this->translateName($af->getName())] = $this->processReturn($af);
                }
                break;
            case $return instanceof ReturnType\ObjectsCollectionType:
                $result['type'] = 'objects_collection';
                $result['object'] = $this->processReturn($return->getObjectType());

                foreach ($return->getAdditionalFields() as $af) {
                    $result[$af->getSerializedName() ?: $this->translateName($af->getName())] = $this->processReturn($af);
                }
                break;
            case $return instanceof ReturnType\TypeInterface:
                $result = ['type' => $return->getType()];
                break;
        }

        if (null !== $description = $return->getDescription()) {
            $result['description'] = $description;
        }

        return $result;
    }

    /**
     * @param $data
     * @return ParameterType\TypeInterface
     */
    private function deserializeParameter($data)
    {
        switch (true) {
            case is_string($data['type']):
                $result = new ParameterType\ScalarType();
                $result->setName($data['name']);
                $result->setType(strstr($data['type'], '|') ? explode('|', $data['type']) : $data['type']);

                if (array_key_exists('default', $data)) {
                    $result->setDefault($data['default']);
                }
                if (!array_key_exists('optional', $data) || false === $data['optional']) {
                    $result->setRequired(true);
                }
                if (array_key_exists('min', $data)) {
                    $result->setOption('min', $data['min']);
                }
                if (array_key_exists('max', $data)) {
                    $result->setOption('max', $data['max']);
                }
                if (array_key_exists('description', $data)) {
                    $result->setDescription($data['description']);
                }
                break;
            case (is_array($data['type'])):
                $result = new ParameterType\ObjectType();
                $result->setName($data['name']);

                if (!empty($data['type'])) {
                    foreach ($data['type'] as $field) {
                        $result->addField($this->deserializeParameter($field));
                    }
                }
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unknown parameter type %s of %s.', gettype($data['type'], $data['name'])));
        }

        return $result;
    }

    /**
     * @param string $name
     * @param array $data
     * @return Service
     */
    private function deserializeService($name, $data)
    {
        $result = new Service($name);

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
            foreach ($data['parameters'] as $parameter) {
                $result->addParameter($this->deserializeParameter($parameter));
            }
        }
        if (!empty($data['returns'])) {
            if (!empty($data['returns']['type'])) {
                $result->addReturn($this->deserializeReturn($data['returns']));
            } else {
                throw new \RuntimeException('Processing of return section implemented only for type section now.');
            }
        }
        if (!empty($data['description'])) {
            $result->setDescription($data['description']);
        }

        return $result;
    }

    /**
     * @param array $data
     * @param null|string $name
     * @return ReturnType\TypeInterface
     */
    private function deserializeReturn($data, $name = null)
    {
        switch (true) {
            case (is_string($data['type'])):
                if ('timestamp' == $data['type']) {
                    $data['type'] = 'DateTime'; // trollface
                }
                if (class_exists($class = 'JayceeSoftware\RpcBundle\Schema\Service\ReturnType\\'.ucfirst($data['type']).'Type')) {
                    /** @var ReturnType\TypeAbstract $result */
                    $result = new $class();
                } elseif ('objects_collection' == $data['type']) {
                    $result = new ReturnType\ObjectsCollectionType();
                    $result->setObjectType($this->deserializeReturn(['type' => $data['object']]));
                } else {
                    throw new \RuntimeException(sprintf('Unknown returns type %s.', $data['type']));
                }
                break;
            case (is_array($data['type']) && isset($data['type']['type']) && 'list_of_objects' == $data['type']['type']):
                $result = new ReturnType\ListOfObjectsType();
                $result->setObjectType($this->deserializeReturn(['type' => $data['type']['object']]));

                break;
            case (is_array($data['type']) && isset($data['type']['type']) && 'objects_collection' == $data['type']['type']):
                $result = new ReturnType\ObjectsCollectionType();
                $result->setObjectType($this->deserializeReturn(['type' => $data['type']['object']]));

                break;
            case (is_array($data['type'])):
                $result = new ReturnType\ObjectType();

                foreach ($data['type'] as $k => $f) {
                    $result->addField($this->deserializeReturn($f, $k));
                }
                break;
            default:
                throw new \RuntimeException(sprintf('Unknown returns type %s.', $data['type']));
        }
        if (!empty($name)) {
            $result->setName($name);
            $result->setSerializedName($name);
        }
        if (!empty($data['description'])) {
            $result->setDescription($data['description']);
        }

        return $result;
    }

    /**
     * Translate name from camelCase to under_score
     *
     * @param string $name
     * @return string
     */
    private function translateName($name)
    {
        return strtolower(preg_replace('/[A-Z]/', '_\\0', lcfirst($name)));
    }
}
