<?php

namespace HttpEventStoreClient;

interface Client
{
    /**
     * @param $streamId
     *
     * @return string
     */
    public function read($streamId);
}
