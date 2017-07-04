<?php

namespace JayceeSoftware\RpcBundle\Schema\Service\ReturnType;

interface TypeInterface
{
    /**
     * @return string
     */
    public function getType();
    /**
     * @return string
     */
    public function getName();
    /**
     * @return string
     */
    public function getSerializedName();
}
