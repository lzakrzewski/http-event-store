<?php

namespace tests\HttpEventStore\Http;

//Todo: Invalid JSON test-cases
use tests\HttpEventStore\EventStoreTestCase;

class HttpEventStoreTest extends EventStoreTestCase
{
    /** @test */
    public function it_can_read_a_stream()
    {
        $this->assertEmpty($this->eventStore->readStream('stream-id'));
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
}
