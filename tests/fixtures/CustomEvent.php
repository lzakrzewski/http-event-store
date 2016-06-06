<?php

namespace tests\HttpEventStore\fixtures;

use HttpEventStore\WritableToStream;

final class CustomEvent implements WritableToStream
{
    /** @var array */
    private $data;

    /** @var string */
    private $type;

    /**
     * @param string $type
     * @param array  $data
     */
    public function __construct($type, array $data)
    {
        $this->type = $type;
        $this->data = $data;
    }

    /** {@inheritdoc} */
    public function type()
    {
        return $this->type;
    }

    /** {@inheritdoc} */
    public function data()
    {
        return $this->data;
    }
}
