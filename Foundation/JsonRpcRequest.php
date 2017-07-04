<?php

namespace JayceeSoftware\RpcBundle\Foundation;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class JsonRpcRequest extends RpcRequest
{
    /**
     * @var int|string
     */
    protected $id = 1;

    public static function create($uri, $method = 'POST', $parameters = array(), $cookies = array(), $files = array(), $server = array(), $content = null, $type = self::TYPE_HTTP)
    {
        if (!in_array($type, static::$availableTypes)) {
            throw new \InvalidArgumentException('Unknown type.');
        }
        $server = array_replace(
            array(
                'SERVER_NAME'          => 'localhost',
                'SERVER_PORT'          => 80,
                'HTTP_HOST'            => 'localhost',
                'HTTP_USER_AGENT'      => 'Symfony/2.X',
                'HTTP_ACCEPT'          => 'application/json,text/javascript,*/*;q=0.01',
                'HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.5',
                'HTTP_ACCEPT_CHARSET'  => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
                'REMOTE_ADDR'          => '127.0.0.1',
                'SCRIPT_NAME'          => '',
                'SCRIPT_FILENAME'      => '',
                'SERVER_PROTOCOL'      => 'JSONRPC/2.0',
                'REQUEST_TIME'         => time(),
                'CONTENT_TYPE'         => 'application/json',
                'REQUEST_TYPE'         => $type,
                'USER_TOKEN'           => null
            ),
            $server,
            static::$typeDefaults[$type]
        );
        $method = 'POST'; // method can be only POST in RPC arch
        return parent::create($uri, $method, $parameters, $cookies, $files, $server, $content);
    }

    public static function createFromGlobals()
    {
        $request = parent::createFromGlobals();

        if (!$request->isMethod('POST')) {
            JsonRpcException::throwByCode(JsonRpcException::ERROR_CODE_INVALID_REQUEST, 'POST allowed only.');
        }
        $request->server->add(array(
            'CONTENT_TYPE'         => 'application/json',
            'REQUEST_TYPE'         => static::TYPE_HTTP,
            'SERVER_PROTOCOL'      => 'JSONRPC/2.0',
        ));
        $rawPostData = $request->getContent();

        if (empty($rawPostData)) {
            JsonRpcException::throwByCode(JsonRpcException::ERROR_CODE_INVALID_REQUEST);
        }
        $jsonEncoder = new JsonEncoder();
        $post = $jsonEncoder->decode($rawPostData, JsonEncoder::FORMAT);

        if (empty($post['method'])) {
            JsonRpcException::throwByCode(JsonRpcException::ERROR_CODE_METHOD_NOT_FOUND, '"method" section required.');
        }
        if (isset($post['id'])) {
            $request->setId($post['id']);
        }
        $request->server->set('REQUEST_URI', '/' . str_replace('.', '/', $post['method']));

        // @todo hack! to setup SSL params. it should be moved to nginx config
        if (!$request->server->has('SSL_CLIENT_S_DN_Email') && $request->server->has('SSL_CLIENT_S_DN')) {
            foreach (explode('/', $request->server->get('SSL_CLIENT_S_DN')) as $p) {
                if (strstr($p, '=')) {
                    list($k, $v) = explode('=', $p);

                    if ($k === 'emailAddress') {
                        $request->server->set('SSL_CLIENT_S_DN_Email', $v);
                    }
                }
            }
        }
        $request->isDebug(empty($post['debug']) ? false : true);
        $request->request = new ParameterBag(empty($post['params']) ? array() : $post['params']);

        return $request;
    }

    /**
     * @return string
     */
    public function getRequestType()
    {
        return $this->server->get('REQUEST_TYPE');
    }

    /**
     * @return bool
     */
    public function isJsonRpcRequest()
    {
        return true;
    }

    /**
     * @param int|string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }
}
