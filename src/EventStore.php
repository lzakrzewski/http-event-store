<?php

namespace HttpEventStore;

use HttpEventStore\Exception\EventStoreException;

interface EventStore
{
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
     *
     * @throws EventStoreException
     *
     * @param $contents
     */
    public function writeStream($streamId, $contents);

    /**
     * @param $eventUri
     *
     * @throws EventStoreException
     *
     * @return string
     */
    public function readEvent($eventUri);

    /**
     * @param $streamId
     *
     * @throws EventStoreException
     */
    public function deleteStream($streamId);
}
