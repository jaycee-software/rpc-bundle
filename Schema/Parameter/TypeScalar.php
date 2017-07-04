<?php

namespace JayceeSoftware\RpcBundle\Schema\Parameter;

use Symfony\Component\Validator\Validator;
use JayceeSoftware\RpcBundle\Configuration\RpcRequest\Parameter as ParameterConfiguration;
use Symfony\Component\HttpFoundation\Request;

class TypeScalar implements TypeInterface
{
    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @param Validator $validator
     */
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParameterConfiguration $configuration)
    {
        if ('scalar' !== $configuration->type) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParameterConfiguration $configuration)
    {
        $options = $this->getOptions($configuration);
        $value = $request->get($configuration->name, $configuration->default);

        if (null === $value) {
            return $configuration->required ? ['This value should not be blank.'] : [];
        }

        $validationResult = $this->validator->validateValue($value, $options['constraints']);
        $result = [];

        foreach ($validationResult as $vr) {
            $result[] = $vr->getMessage();
        }

        return $result;
    }

    /**
     * @param ParameterConfiguration $configuration
     * @return array
     */
    protected function getOptions(ParameterConfiguration $configuration)
    {
        return array_replace(array(
            'constraints' => []
        ), $configuration->options);
    }
}
