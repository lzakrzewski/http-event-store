<?php

namespace HttpEventStore\Http;

use HttpEventStore\Projection;

class HttpProjection implements Projection
{
    /** {@inheritdoc} */
    public function createProjection($projectionId, $query)
    {
    }

    /** {@inheritdoc} */
    public function readProjection($projectionId)
    {
    }
}