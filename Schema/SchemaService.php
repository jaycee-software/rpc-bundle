<?php

namespace JayceeSoftware\RpcBundle\Schema;

class SchemaService
{
    /**
     * @var Formatter\FormatterInterface[]
     */
    private $formatters = [];

    public function __construct()
    {

    }

    /**
     * @param  Formatter\FormatterInterface $formatter
     * @param  string                       $name
     * @throws \InvalidArgumentException
     */
    public function addFormatter(Formatter\FormatterInterface $formatter, $name)
    {
        if (array_key_exists($name, $this->formatters)) {
            throw new \InvalidArgumentException(sprintf('Schema formatter "%s" already defined as "%s".', $name, get_class($this->formatters[$name])));
        }
        $this->formatters[$name] = $formatter;
    }

    public function getSchema($name)
    {

    }

    /**
     * @param  Schema $schema
     * @param  string $format
     * @return mixed
     */
    public function format(Schema $schema, $format)
    {
        return $this->getFormatter($format)->format($schema);
    }

    /**
     * @param array $data
     * @param string $format
     * @return Schema
     */
    public function deserialize($data, $format)
    {
        return $this->getFormatter($format)->deserialize($data);
    }

    /**
     * @param  string                       $name
     * @return Formatter\FormatterInterface
     * @throws \InvalidArgumentException
     */
    protected function getFormatter($name)
    {
        if (!array_key_exists($name, $this->formatters)) {
            throw new \InvalidArgumentException(sprintf('Schema formatter "%s" was not registered.', $name));
        }

        return $this->formatters[$name];
    }
}
