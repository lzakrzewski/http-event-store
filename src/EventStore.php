<?php

namespace HttpEventStore;

use HttpEventStore\Exception\EventStoreException;

interface EventStore
{
    const STREAM_DOES_NOT_EXIST = 404;
    const REQUEST_BODY_INVALID  = 400;

    /**
     * @param $streamId
     *
     * @throws EventStoreException
     *
     * @return string
     */
    public function readStream($streamId);

    /**
     * @param $streamId
     * @param WritableToStream[] $events
     *
     * @throws EventStoreException
     */
    public function writeStream($streamId, array $events);

    /**
     * @param $streamId
     *
     * @throws EventStoreException
     */
    public function deleteStream($streamId);
}
