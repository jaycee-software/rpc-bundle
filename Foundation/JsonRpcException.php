<?php

namespace JayceeSoftware\RpcBundle\Foundation;

class JsonRpcException extends \Exception
{
    const ERROR_CODE_PARSE_ERROR      = -32700;
    const ERROR_CODE_INVALID_REQUEST  = -32600;
    const ERROR_CODE_METHOD_NOT_FOUND = -32601;
    const ERROR_CODE_INVALID_PARAMS   = -32602;
    const ERROR_CODE_INTERNAL_ERROR   = -32603;
    const ERROR_CODE_RUNTIME_ERROR    = -32001;
    const ERROR_CODE_VALIDATION_ERROR = -32002;

    public static $errorMessages = array(
        self::ERROR_CODE_PARSE_ERROR      => 'Parse error',
        self::ERROR_CODE_INVALID_REQUEST  => 'Invalid Request',
        self::ERROR_CODE_METHOD_NOT_FOUND => 'Method not found',
        self::ERROR_CODE_INVALID_PARAMS   => 'Invalid params',
        self::ERROR_CODE_INTERNAL_ERROR   => 'Internal error',
        self::ERROR_CODE_RUNTIME_ERROR    => 'Runtime error',
        self::ERROR_CODE_VALIDATION_ERROR => 'Validation error',
    );

    protected $data = array();

    /**
     * @param $code
     * @param string $message
     * @param null|\Exception $previous
     * @param array $data
     * @return JsonRpcException
     */
    public static function createByCode($code, $message = '', \Exception $previous = null, array $data = array())
    {
        if (isset(static::$errorMessages[$code])) {
            $e = new self(empty($message) ? static::$errorMessages[$code] : $message, $code, $previous);
        } else {
            $e = new self($message, $code, $previous);
        }
        $e->setData($data);

        return $e;
    }

    /**
     * @param $code
     * @param string $message
     * @param null|\Exception $previous
     * @param array $data
     * @throws JsonRpcException
     */
    public static function throwByCode($code, $message = '', \Exception $previous = null, array $data = array())
    {
        throw static::createByCode($code, $message, $previous, $data);
    }

    /**
     * @param array $data
     * @return JsonRpcException
     */
    public function setData(array $data = array())
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param $data
     * @return JsonRpcException
     */
    public function addData($data)
    {
        $this->data[] = $data;
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

}
