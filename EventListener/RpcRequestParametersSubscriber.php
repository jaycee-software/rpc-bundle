<?php

namespace JayceeSoftware\RpcBundle\EventListener;

use JayceeSoftware\RpcBundle\Foundation\JsonRpcException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use JayceeSoftware\RpcBundle\Foundation\JsonRpcResponse;
use JayceeSoftware\RpcBundle\Schema\SchemaManager;

class RpcRequestParametersSubscriber implements EventSubscriberInterface
{
    /**
     * @var SchemaManager
     */
    private $manager;

    /**
     * @param SchemaManager $manager
     */
    public function __construct(SchemaManager $manager)
    {
        $this->manager = $manager;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) {
            return;
        }

        $request = $event->getRequest();

        if (!$configuration = $request->attributes->get('_rpc_request_parameters')) {
            return;
        }

        $errors = $this->manager->apply($request, is_array($configuration) ? $configuration : array($configuration));

        if (count($errors)) {
            throw JsonRpcException::createByCode(JsonRpcException::ERROR_CODE_INVALID_PARAMS, '', null, $errors);
        }
        $request->attributes->set('_rpc_response_serializer_groups', [str_replace('/', '.', trim($request->getRequestUri(), '/')), 'Default']);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => array('onKernelController', -130),
        );
    }
}
