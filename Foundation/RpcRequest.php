<?php

namespace JayceeSoftware\RpcBundle\Foundation;

use Symfony\Component\HttpFoundation\Request;

abstract class RpcRequest extends Request
{
    const TYPE_HTTP = 'http';
    const TYPE_AMQP = 'amqp';

    /**
     * @var bool
     */
    protected $debug = false;

    public static $availableTypes = array(self::TYPE_HTTP, self::TYPE_AMQP);

    protected static $typeDefaults = array(
        self::TYPE_AMQP => array(
            'server' => array(
                'HTTP_USER_AGENT' => 'AMQP'
            )
        ),
        self::TYPE_HTTP => array(
            'server' => array(
            )
        )
    );

    /**
     * @param null|bool $debug
     * @return bool|JsonRpcRequest
     */
    public function isDebug($debug = null)
    {
        if (null === $debug) {
            return $this->debug;
        } else {
            $this->debug = (bool) $debug;
            return $this;
        }
    }
}
