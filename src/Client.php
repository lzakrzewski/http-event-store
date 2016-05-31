<?php

namespace HttpEventStoreClient;

interface Client
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

    /**
     * @param $projectionId
     * @param $query
     *
     * @throws EventStoreException
     * 
     * @return string
     */
    public function createProjection($projectionId, $query);

    /**
     * @param $projectionId
     *
     * @throws EventStoreException
     * 
     * @return string
     */
    public function readProjection($projectionId);
}
