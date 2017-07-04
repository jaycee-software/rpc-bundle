<?php

namespace JayceeSoftware\RpcBundle\Schema\Parameter;

use Symfony\Component\Validator\Validator;
use JMS\Serializer\Serializer;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use JayceeSoftware\RpcBundle\Configuration\RpcRequest\Parameter as ParameterConfiguration;
use Symfony\Component\HttpFoundation\Request;

class TypeObject implements TypeInterface
{
    /**
     * @var Validator
     */
    protected $validator;
    /**
     * @var Serializer
     */
    protected $serializer;
    /**
     * @var PropertyNamingStrategyInterface
     */
    protected $propertyNamingStrategy;

    public static $paramConstraints = array(
        'Symfony\Component\Validator\Constraints\NotBlank'
    );

    /**
     * @param Validator $validator
     * @param Serializer $serializer
     * @param PropertyNamingStrategyInterface $propertyNamingStrategy
     */
    public function __construct(Validator $validator, Serializer $serializer, PropertyNamingStrategyInterface $propertyNamingStrategy)
    {
        $this->validator = $validator;
        $this->serializer = $serializer;
        $this->propertyNamingStrategy = $propertyNamingStrategy;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParameterConfiguration $configuration)
    {
        if ('object' !== $configuration->type) {
            return false;
        }
        $options = $this->getOptions($configuration);

        if (null === $options['class']) {
            return false;
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParameterConfiguration $configuration)
    {
        $options = self::getOptions($configuration);
        $value = $request->get($configuration->name, $configuration->default);

        if (null === $value) {
            return $configuration->required ? ['This value should not be blank.'] : [];
        }
        /** @var \Symfony\Component\Validator\Mapping\ClassMetadata $meta */
        $meta = $this->validator->getMetadataFor($options['class']);
        $serializerMeta = $this->serializer->getMetadataFactory()->getMetadataForClass($options['class']);
        $result = [];
        $visited = [];

        foreach ($meta->properties as $property) {
            $constraints = $property->findConstraints($options['group']);
            $serializedPropertyName = isset($serializerMeta->propertyMetadata[$property->name])
                ? $this->propertyNamingStrategy->translateName($serializerMeta->propertyMetadata[$property->name])
                : $property->name
            ;

            // handle optional param
            if (!array_key_exists($serializedPropertyName, $value)) {
                foreach ($constraints as $constraint) {
                    if ($constraint instanceof \FXS\Bundles\ValidatorBundle\Constraints\RpcRequest\OptionalParameter) {
                        continue 2;
                    }
                }
            }

            foreach ($constraints as $constraint) {
                if (in_array(get_class($constraint), self::$paramConstraints)) {
                    $validationResult = $this->validator->validateValue(
                        array_key_exists($serializedPropertyName, $value) ? $value[$serializedPropertyName] : null,
                        $constraint,
                        $options['group']
                    );

                    if (count($validationResult) > 0) {
                        $result[$serializedPropertyName] = $validationResult[0]->getMessage();
                    }
                }
            }

            $visited[$property->name] = $property->name;
        }
        // set prepared value
        $request->request->set($configuration->name, $value);

        return $result;
    }

    /**
     * @param ParameterConfiguration $configuration
     * @return array
     */
    public static function getOptions(ParameterConfiguration $configuration)
    {
        return array_replace(array(
            'class'          => null,
            'group'          => 'Default',
            'exclude'        => array(), // ?
            'mapping'        => array(), // ?
            'strip_null'     => false,   // ?
        ), $configuration->options);
    }
}
