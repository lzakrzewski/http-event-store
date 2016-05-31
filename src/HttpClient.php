<?php

namespace HttpEventStoreClient;

class HttpClient implements Client
{
    /** {@inheritdoc} */
    public function readStream($streamId)
    {
        return 'contents';
    }

    /** {@inheritdoc} */
    public function writeStream($streamId, $contents)
    {
    }

    /** {@inheritdoc} */
    public function readEvent($eventUri)
    {
    }

    /** {@inheritdoc} */
    public function deleteStream($streamId)
    {
    }

    /** {@inheritdoc} */
    public function createProjection($projectionId, $query)
    {
    }

    /** {@inheritdoc} */
    public function readProjection($projectionId)
    {
    }
}
