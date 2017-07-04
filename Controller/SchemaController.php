<?php

namespace JayceeSoftware\RpcBundle\Controller;

use JayceeSoftware\RpcBundle\Foundation\JsonRpcException;
use JayceeSoftware\RpcBundle\Foundation\JsonRpcResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use JayceeSoftware\RpcBundle\Configuration\RpcResponse;

class SchemaController extends Controller
{
    /**
     * @param Request $request
     * @return mixed
     * @throws \JayceeSoftware\RpcBundle\Foundation\JsonRpcException
     * @RpcResponse
     */
    public function getAction(Request $request)
    {
        $schemaCache = $this->get('jaycee_software_rpc.schema.cache');

        if (!$schema = $schemaCache->fetch($request->attributes->get('_service_name'))) {
            return null;
        }

        $schemaService = $this->get('jaycee_software_rpc.schema.service');
        // @todo add wsdl implementation
        $formatted = $schemaService->format($schema, 'smd');

        $result = json_decode($formatted, true);

        return $result;
    }
}
