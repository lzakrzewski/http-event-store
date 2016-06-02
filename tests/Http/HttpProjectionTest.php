<?php

namespace tests\HttpEventStore\Http;

use HttpEventStore\Exception\EventStoreConnectionFailed;
use HttpEventStore\WritableEvent;
use Ramsey\Uuid\Uuid;
use tests\HttpEventStore\EventStoreTestCase;

class HttpProjectionTest extends EventStoreTestCase
{
    /** @test */
    public function it_can_create_a_projection()
    {
        $streamId = Uuid::uuid4()->toString();
        $projectionId = 'projection-'.$streamId;

        $this->given([
            $streamId => [
                new WritableEvent('event1', ['message' => 'text1']),
                new WritableEvent('event2', ['message' => 'text2']),
            ],
        ]);

        $this->projection->createProjection($projectionId, $this->countEventsQuery($streamId));

        $this->assertThatProjectionExists($projectionId);
    }

    /** @test */
    public function it_reads_projection()
    {
        $streamId = Uuid::uuid4()->toString();
        $projectionId = 'projection-'.$streamId;

        $this->given([
            $streamId => [
                new WritableEvent('event1', ['message' => 'text1']),
                new WritableEvent('event2', ['message' => 'text2']),
            ],
        ]);

        $this->projection->createProjection($projectionId, $this->countEventsQuery($streamId));

        $this->assertEquals(['count' => 2], $this->projection->readProjection($projectionId));
    }

    /** @test */
    public function it_reads_empty_projection()
    {
        $streamId = Uuid::uuid4()->toString();
        $projectionId = 'projection-'.$streamId;

        $this->projection->createProjection($projectionId, $this->countEventsQuery($streamId));

        $this->assertEquals(null, $this->projection->readProjection($projectionId));
    }

    /** @test */
    public function it_fails_when_event_store_connection_failed_during_creating_a_projection()
    {
        $this->expectException(EventStoreConnectionFailed::class);
        
        $streamId = Uuid::uuid4()->toString();
        $projectionId = 'projection-'.$streamId;

        $this->given([
            $streamId => [
                new WritableEvent('event1', ['message' => 'text1']),
                new WritableEvent('event2', ['message' => 'text2']),
            ],
        ]);

        $this->givenEventStoreFailed();

        $this->projection->createProjection($projectionId, $this->countEventsQuery($streamId));
    }

    /** @test */
    public function it_fails_when_event_store_connection_failed_during_reading_a_projection()
    {
        $this->expectException(EventStoreConnectionFailed::class);
        
        $streamId = Uuid::uuid4()->toString();
        $projectionId = 'projection-'.$streamId;
        
        $this->givenEventStoreFailed();

        $this->projection->readProjection($projectionId);
    }

    private function countEventsQuery($streamId)
    {
        return <<<STR
fromStream('$streamId').
    when({
       \$init : function(s,e) {return {count : 0}},
       \$any  : function(s,e) {return {count : s.count +1}}
    })
;
STR;
    }
}
