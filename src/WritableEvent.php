<?php

namespace HttpEventStore;

class WritableEvent implements WritableToStream
{
    /** @var string */
    private $type;
    
    /** @var array */
    private $data;

    /**
     * @param string $type
     * @param array $data
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
