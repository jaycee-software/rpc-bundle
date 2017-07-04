<?php

namespace JayceeSoftware\RpcBundle\Schema\Parameter;

use JayceeSoftware\RpcBundle\Configuration\RpcRequest\Parameter as ParameterConfiguration;
use Symfony\Component\HttpFoundation\Request;

class TypeListOfObjects implements TypeInterface
{
    /**
     * @var string
     */
    private $requestSection;

    /**
     * @param string $requestSection
     */
    public function __construct($requestSection)
    {
        $this->requestSection = $requestSection;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParameterConfiguration $configuration)
    {
        if ('list_of_objects' != $configuration->type) {
            return false;
        }

        $options = self::getOptions($configuration);

        if (null === $options['object_class']) {
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

        $limit = $request->request->get($options['limitPath'], $options['limitDefault'], true);

        if (!in_array($limit, $options['limitAvailable'])) {
            return [sprintf('Limit "%s" is not allowed. Allowed limits: "%s".', $limit, join('", "', $options['limitAvailable']))];
        }

        $request->attributes->add([$this->requestSection => [
                'page'  => $request->request->get($options['pagePath'], 1, true),
                'limit' => $limit,
                'sort'  => $request->request->get($options['sortPath'], [], true)
            ]]);

        $request->request->remove($options['pagePath']);
        $request->request->remove($options['limitPath']);
        $request->request->remove($options['sortPath']);

        return [];
    }

    /**
     * @param ParameterConfiguration $configuration
     * @return array
     */
    public static function getOptions(ParameterConfiguration $configuration)
    {
        return array_replace(array(
                'object_class'      => null,
                'pagePath'          => 'page',
                'limitPath'         => 'limit',
                'limitDefault'      => 20,
                'limitAvailable'    => [20, 50, 100],
                'sortPath'          => 'sort',
            ), $configuration->options);
    }
}
