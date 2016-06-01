<?php

namespace HttpEventStore;

use HttpEventStore\Exception\EventStoreException;

interface Projection
{
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