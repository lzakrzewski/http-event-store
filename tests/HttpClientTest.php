<?php

namespace tests\HttpEventStoreClient;

//Todo: Invalid JSON test-cases
class HttpClientTest extends EventStoreTestCase
{
    /** @test */
    public function it_can_read_a_stream()
    {
        $streamId = 'stream-id';
        
        $this->assertEquals('contents', $this->client->readStream($streamId));
    }

    /** @test */
    public function it_can_read_an_empty_stream()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function it_fails_when_stream_does_not_exists_during_reading_a_stream()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function it_can_write_a_stream()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function it_can_write_a_stream_already_written()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function it_fails_when_stream_does_not_exists_during_a_writing_stream()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function it_can_delete_a_stream()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function it_can_delete_a_stream_already_written()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function it_fails_when_a_stream_does_not_exists_during_deleting_stream()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function it_can_read_an_event()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function it_fails_when_an_event_does_not_exists_during_reading_event()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function it_can_create_a_projection()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function it_read_projection()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function it_read_empty_projection()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function it_fails_when_a_projection_does_not_exists_during_reading_projection()
    {
        $this->markTestIncomplete();
    }
}
