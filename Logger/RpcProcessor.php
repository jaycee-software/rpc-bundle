<?php

namespace JayceeSoftware\RpcBundle\Logger;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

class RpcProcessor
{
    private $serverData = [];

    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($event->isMasterRequest()) {
            $this->serverData = $event->getRequest()->server->all();
        }
    }

    public function onKernelTerminate(PostResponseEvent $event)
    {
        $this->serverData = [];
    }

    public function processRecord(array $record)
    {
        if (isset($this->serverData['SERVICE_NAME'])) {
            $record['extra']['service_name'] = $this->serverData['SERVICE_NAME'];
        }

        return $record;
    }
}
