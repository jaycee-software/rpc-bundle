<?php

namespace JayceeSoftware\RpcBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use JMS\Serializer\Serializer;
use JayceeSoftware\RpcBundle\Foundation\JsonRpcResponse;

class RpcResponseSubscriber implements EventSubscriberInterface
{
    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @param Serializer $serializer
     */
    public function __construct($serializer)
    {
        $this->serializer = $serializer;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) {
            return;
        }

        $request = $event->getRequest();

        if (!$configuration = $request->attributes->get('_rpc_response')) {
            return;
        }

        $request->attributes->set('_rpc_response_serializer_groups', [str_replace('/', '.', trim($request->getRequestUri(), '/')), 'Default']);
    }

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();

        if (!$configuration = $request->attributes->get('_rpc_response')) {
            return;
        }

        $response = new JsonRpcResponse($event->getControllerResult());
        $response->setEncoder($this->serializer);

        if ($groups = $request->attributes->get('_rpc_response_serializer_groups')) {
            $response->setSerializationGroups($groups);
        }

        $event->setResponse($response);
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $request = $event->getRequest();

        if (!$configuration = $request->attributes->get('_rpc_response')) {
            return;
        }
        $exception = $event->getException();

        $response = JsonRpcResponse::createFromException($exception);

        $event->setResponse($response);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => array('onKernelController', -130),
            KernelEvents::VIEW => 'onKernelView',
            //KernelEvents::EXCEPTION => array('onKernelException', -130),
        );
    }
}
