<?php

namespace JayceeSoftware\RpcBundle\Schema\Formatter;

use JayceeSoftware\RpcBundle\Schema\Schema;

interface FormatterInterface
{
    /**
     * Format schema to concrete format
     *
     * @param Schema $schema
     * @return mixed
     */
    public function format(Schema $schema);

    /**
     * Deserialize schema from concrete format
     *
     * @param mixed $data
     * @return Schema
     */
    public function deserialize($data);
}
