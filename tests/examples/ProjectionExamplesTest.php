<?php

namespace tests\HttpEventStore\examples;

use HttpEventStore\Http\HttpProjection;
use Ramsey\Uuid\Uuid;
use tests\HttpEventStore\EventStoreTestCase;

class ProjectionExamplesTest extends EventStoreTestCase
{
    /** @test */
    public function object_creation_with_factory_method()
    {
        $projection = \HttpEventStore\Http\HttpProjection::create('localhost', '2113', 'admin', 'changeit');

        $this->assertInstanceOf(HttpProjection::class, $projection);
    }

    /** @test */
    public function object_creation_with_dependency_injection()
    {
        $guzzle     = new \GuzzleHttp\Client();
        $httpClient = new \HttpEventStore\Http\HttpClient($guzzle, 'localhost', '2113', 'admin', 'changeit');
        $projection = new \HttpEventStore\Http\HttpProjection($httpClient);

        $this->assertInstanceOf(HttpProjection::class, $projection);
    }

    /** @test */
    public function usage()
    {
        $streamId = Uuid::uuid4()->toString();

        $eventStore = \HttpEventStore\Http\HttpEventStore::create('localhost', '2113');
        $event1     = new \HttpEventStore\WritableEvent('productWasAddedToBasket', ['productId' => 'product1', 'name' => 'Teapot']);
        $event2     = new \HttpEventStore\WritableEvent('productWasRemovedFromBasket', ['productId' => 'product1']);

        // Writing to a Stream
        $eventStore->writeStream($streamId, [$event1, $event2]);

        // Creating a projection
        $projection = \HttpEventStore\Http\HttpProjection::create('localhost', '2113', 'admin', 'changeit');

        $countOfEventsQuery = <<<STR
fromStream('$streamId').
    when({
       \$init : function(s,e) {return {count : 0}},
       \$any  : function(s,e) {return {count : s.count +1}}
    })
;
STR;
        $projectionId = 'projection-'.$streamId;
        $projection->createProjection($projectionId, $countOfEventsQuery);

        // Reading of a projection
        $countOfEvents = $projection->readProjection($projectionId);

        $this->assertEquals(["count" => 2], $countOfEvents);
    }
}
