<?php

namespace JayceeSoftware\RpcBundle\Foundation;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;

class JsonRpcResponse extends Response
{
    /**
     * @var JsonEncoder|Serializer
     */
    protected $encoder;

    /**
     * @var null|int|string
     */
    protected $id = null;

    /**
     * Serializer groups
     *
     * @var array
     */
    protected $serializationGroups = ['Default'];

    /**
     * @var array
     * @static
     */
    protected static $errorToStatus = array(
        JsonRpcException::ERROR_CODE_PARSE_ERROR      => 400,
        JsonRpcException::ERROR_CODE_INVALID_REQUEST  => 400,
        JsonRpcException::ERROR_CODE_METHOD_NOT_FOUND => 404,
        JsonRpcException::ERROR_CODE_INVALID_PARAMS   => 400,
        JsonRpcException::ERROR_CODE_INTERNAL_ERROR   => 500
    );

    /**
     * Create response from Exception
     *
     * @param \Exception $e
     * @return JsonRpcResponse
     */
    public static function createFromException(\Exception $e)
    {
        $code = $e->getCode();

        $content = array(
            'code' => $code,
            'message' => $e->getMessage(),
            'data' => array()
        );

        if ($e instanceof JsonRpcException) {
            $content['data'] = $e->getData();
        }

        return new static(
            $content,
            isset(static::$errorToStatus[$code]) ? static::$errorToStatus[$code] : 500
        );
    }

    /**
     * @param string|array|object $content Serializable content
     * @param int $status
     * @param array $headers
     */
    public function __construct($content = '', $status = 200, $headers = array())
    {
        $this->headers = new ResponseHeaderBag($headers);
        $this->setContent($content);
        $this->setStatusCode($status);
        $this->setProtocolVersion('2.0');
        if (!$this->headers->has('Date')) {
            $this->setDate(new \DateTime(null, new \DateTimeZone('UTC')));
        }
    }

    /**
     * @param JsonEncoder|Serializer $encoder
     * @return JsonRpcResponse
     * @throws \InvalidArgumentException
     */
    public function setEncoder($encoder)
    {
        if (!$encoder instanceof JsonEncoder && !$encoder instanceof Serializer) {
            throw new \InvalidArgumentException();
        }
        $this->encoder = $encoder;

        return $this;
    }

    /**
     * @return JsonEncoder|Serializer
     */
    protected function getEncoder()
    {
        if (null === $this->encoder) {
            $this->encoder = new JsonEncoder();
        }

        return $this->encoder;
    }

    /**
     * @param array $serializationGroups
     * @return JsonRpcResponse
     */
    public function setSerializationGroups($serializationGroups)
    {
        $this->serializationGroups = array_unique(array_merge(array_values($serializationGroups), $this->serializationGroups));

        return $this;
    }

    /**
     * Get prepared response
     *
     * @param Request $request
     * @return JsonRpcResponse
     */
    public function prepare(Request $request)
    {
        $this->headers->set('Content-Type', 'application/json');
        parent::prepare($request);

        if ($request instanceof JsonRpcRequest) {
            $this->id = $request->getId();
        }
        $this->setProtocolVersion('2.0');

        return $this;
    }

    /**
     * Sends content for the current web response.
     *
     * @return Response
     */
    public function sendContent()
    {
        echo $this->__toString();

        return $this;
    }

    /**
     * Sets the response content. Must be serializable to JSON string
     *
     * @param mixed $content
     * @return Response
     *
     * @throws \UnexpectedValueException
     */
    public function setContent($content)
    {
        if (!$this->isSerializable($content)) {
            throw new \UnexpectedValueException('The Response content must be serializable to JSON.');
        }

        $this->content = $content;

        return $this;
    }

    public function __toString()
    {
        $response = array(
            'jsonprc' => $this->getProtocolVersion(),
            'id' => $this->id
        );
        if ($this->isOk()) {
            $response['result'] = $this->content;
        } else {
            $response['error'] = $this->content;
        }

        return $this->encode($response);
    }

    protected function encode($data)
    {
        $encoder = $this->getEncoder();

        if ($encoder instanceof JsonEncoder) {
            return $encoder->encode($data, JsonEncoder::FORMAT);
        } else {
            return $encoder->serialize($data, JsonEncoder::FORMAT, $this->getSerializationContext());
        }
    }

    private function getSerializationContext()
    {
        $context = SerializationContext::create()
            ->setGroups($this->serializationGroups)
        ;

        return $context;
    }

    /**
     * Can var be encoded to JSON
     *
     * @param $var
     * @return bool
     */
    private function isSerializable($var)
    {
        try {
            json_encode($var);
            return true;
        } catch (\Exception $e) {
        }
        return false;
    }

}
