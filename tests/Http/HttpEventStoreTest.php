<?php

namespace tests\HttpEventStore\Http;

use HttpEventStore\Exception\CannotWriteStreamWithoutEvents;
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
    public function it_does_not_read_events_from_another_stream()
    {
        $streamId1 = Uuid::uuid4()->toString();
        $streamId2 = Uuid::uuid4()->toString();

        $this->given([
            $streamId1 => [
                new WritableEvent('event1', ['message' => 'text1']),
                new WritableEvent('event2', ['message' => 'text2']),
            ],
            $streamId2 => [
                new WritableEvent('event3', ['message' => 'text3']),
                new WritableEvent('event4', ['message' => 'text4']),
            ],
        ]);

        $this->assertEquals(
            [
                new WritableEvent('event1', ['message' => 'text1']),
                new WritableEvent('event2', ['message' => 'text2']),
            ],
            $this->eventStore->readStream($streamId1)
        );
    }

    /** @test */
    public function it_fails_when_stream_does_not_exist_during_reading_a_stream()
    {
        $this->expectException(StreamDoesNotExist::class);

        $streamId = Uuid::uuid4()->toString();

        $this->eventStore->readStream($streamId);
    }

    /** @test */
    public function it_fails_when_event_store_connection_failed_during_reading_a_stream()
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
        $streamId = Uuid::uuid4()->toString();

        $this->eventStore->writeStream(
            $streamId,
            [
                new WritableEvent('event1', ['message' => 'text1']),
                new WritableEvent('event2', ['message' => 'text2']),
            ]
        );

        $this->assertThatStreamContainsEvents(
            [
                new WritableEvent('event1', ['message' => 'text1']),
                new WritableEvent('event2', ['message' => 'text2']),
            ],
            $streamId
        );
    }

    /** @test */
    public function it_can_not_write_a_stream_without_events()
    {
        $this->expectException(CannotWriteStreamWithoutEvents::class);

        $streamId = Uuid::uuid4()->toString();

        $this->eventStore->writeStream($streamId, []);
    }

    /** @test */
    public function it_can_append_a_stream()
    {
        $streamId = Uuid::uuid4()->toString();

        $this->given([
            $streamId => [
                new WritableEvent('event1', ['message' => 'text1']),
                new WritableEvent('event2', ['message' => 'text2']),
            ],
        ]);

        $this->eventStore->writeStream(
            $streamId,
            [
                new WritableEvent('event3', ['message' => 'text3']),
                new WritableEvent('event4', ['message' => 'text4']),
            ]
        );

        $this->assertThatStreamContainsEvents(
            [
                new WritableEvent('event1', ['message' => 'text1']),
                new WritableEvent('event2', ['message' => 'text2']),
                new WritableEvent('event3', ['message' => 'text3']),
                new WritableEvent('event4', ['message' => 'text4']),
            ],
            $streamId
        );
    }

    /** @test */
    public function it_fails_when_event_store_connection_failed_during_writing_a_stream()
    {
        $this->expectException(EventStoreConnectionFailed::class);

        $streamId = Uuid::uuid4()->toString();

        $this->givenEventStoreFailed();

        $this->eventStore->writeStream(
            $streamId,
            [
                new WritableEvent('event1', ['message' => 'text1']),
                new WritableEvent('event2', ['message' => 'text2']),
            ]
        );
    }

    /** @test */
    public function it_can_delete_a_stream()
    {
        $streamId = Uuid::uuid4()->toString();

        $this->given([
            $streamId => [new WritableEvent('event1', ['message' => 'text1'])],
        ]);

        $this->eventStore->deleteStream($streamId);

        $this->assertThatStreamDoesNotExist($streamId);
    }

    /** @test */
    public function it_can_delete_a_stream_which_was_already_deleted()
    {
        $streamId = Uuid::uuid4()->toString();

        $this->eventStore->deleteStream($streamId);
    }

    /** @test */
    public function it_fails_when_event_store_connection_failed_during_deleting_stream()
    {
        $this->expectException(EventStoreConnectionFailed::class);

        $streamId = Uuid::uuid4()->toString();

        $this->givenEventStoreFailed();

        $this->eventStore->deleteStream($streamId);
    }
}
