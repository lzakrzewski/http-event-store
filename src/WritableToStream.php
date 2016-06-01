<?php

namespace HttpEventStore;

interface WritableToStream
{
    /**
     * @return string
     */
    public function type();

    /**
     * @return array
     */
    public function data();
}
