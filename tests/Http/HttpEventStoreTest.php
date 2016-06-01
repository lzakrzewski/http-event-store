<?php

namespace tests\HttpEventStore\Http;

use HttpEventStore\Exception\EventStoreConnectionFailed;
use HttpEventStore\Exception\StreamDoesNotExist;
use HttpEventStore\WritableEvent;
use Ramsey\Uuid\Uuid;
use tests\HttpEventStore\EventStoreTestCase;

class HttpEventStoreTest extends EventStoreTestCase
{
    /** @test */
    public function it_can_read_a_stream()
    {
        $streamId = Uuid::uuid4()->toString();

        $this->given([
            $streamId => [
                new WritableEvent('event1', ['message' => 'text1']),
                new WritableEvent('event2', ['message' => 'text2']),
            ],
        ]);

        $this->assertEquals(
            [
                new WritableEvent('event1', ['message' => 'text1']),
                new WritableEvent('event2', ['message' => 'text2']),
            ],
            $this->eventStore->readStream($streamId)
        );
    }

    /** @test */
    public function it_fails_when_event_store_does_not_exist_during_reading_a_stream()
    {
        $this->expectException(StreamDoesNotExist::class);

        $streamId = Uuid::uuid4()->toString();

        $this->eventStore->readStream($streamId);
    }

    /** @test */
    public function it_fails_when_event_store_fails_during_reading_a_stream()
    {
        $this->expectException(EventStoreConnectionFailed::class);

        $streamId = Uuid::uuid4()->toString();

        $this->given([
            $streamId => [
                new WritableEvent('event1', ['message' => 'text1']),
                new WritableEvent('event2', ['message' => 'text2']),
            ],
        ]);

        $this->givenEventStoreFailed();

        $this->eventStore->readStream($streamId);
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
