<?php

namespace HttpEventStoreClient;

class HttpClient implements Client
{
    /** {@inheritdoc} */
    public function read($streamId)
    {
        return 'contents';
    }
}
