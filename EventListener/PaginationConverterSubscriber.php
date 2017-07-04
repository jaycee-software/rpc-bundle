<?php

namespace JayceeSoftware\RpcBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Knp\Component\Pager\Pagination\AbstractPagination;

class PaginationConverterSubscriber implements EventSubscriberInterface
{
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();
        $pagination = $event->getControllerResult();

        if (!$configuration = $request->attributes->get('_rpc_response')) {
            return;
        }
        if ('list_of_entities' != $configuration->type) {
            return;
        }
        if (!$pagination instanceof AbstractPagination) {
            return;
        }
        $result = [
            'list' => $pagination->getItems(),
            'pages' => [
                'page'  => $pagination->getCurrentPageNumber(),
                'limit' => $pagination->getItemNumberPerPage(),
                'total' => $pagination->getTotalItemCount()
            ]
        ];

        $event->setControllerResult($result);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::VIEW => array('onKernelView', 100),
        );
    }
}
