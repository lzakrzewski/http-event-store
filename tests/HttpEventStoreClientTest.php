<?php

namespace tests\HttpEventStoreClient;

class HttpEventStoreClientTest extends EventStoreTestCase
{
    /** @test */
    public function it_can_read()
    {
        $streamId = 'stream-id';
        
        $this->assertEquals('contents', $this->client->read($streamId));
    }
}
