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
