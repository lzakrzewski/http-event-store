<?php

namespace HttpEventStore;

use HttpEventStore\Exception\EventStoreException;

interface Projection
{
    const PROJECTION_ALREADY_EXIST = 409;

    /**
     * @param $projectionId
     * @param $query
     *
     * @throws EventStoreException
     */
    public function createProjection($projectionId, $query);

    /**
     * @param $projectionId
     *
     * @throws EventStoreException
     *
     * @return array|null
     */
    public function readProjection($projectionId);
}
