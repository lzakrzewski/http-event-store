<?php

namespace tests\HttpEventStore\examples;

use HttpEventStore\Http\HttpEventStore;
use Ramsey\Uuid\Uuid;
use tests\HttpEventStore\EventStoreTestCase;

class EventStoreExamplesTest extends EventStoreTestCase
{
    /** @test */
    public function object_creation_with_factory_method()
    {
        $eventStore = \HttpEventStore\Http\HttpEventStore::create('localhost', '2113');

        $this->assertInstanceOf(HttpEventStore::class, $eventStore);
    }

    /** @test */
    public function object_creation_with_dependency_injection()
    {
        $guzzle     = new \GuzzleHttp\Client();
        $httpClient = new \HttpEventStore\Http\HttpClient($guzzle, 'localhost', '2113');
        $eventStore = new \HttpEventStore\Http\HttpEventStore($httpClient);

        $this->assertInstanceOf(HttpEventStore::class, $eventStore);
    }

    /** @test */
    public function writing_to_a_stream()
    {
        $streamId = Uuid::uuid4()->toString();

        $eventStore = \HttpEventStore\Http\HttpEventStore::create('localhost', '2113');
        $event1     = new \HttpEventStore\WritableEvent('productWasAddedToBasket', ['productId' => 'product1', 'name' => 'Teapot']);
        $event2     = new \HttpEventStore\WritableEvent('productWasRemovedFromBasket', ['productId' => 'product1']);

        $eventStore->writeStream($streamId, [$event1, $event2]);

        $this->assertThatStreamContainsEvents([$event1, $event2], $streamId);
    }

    /** @test */
    public function reading_from_a_stream()
    {
        $streamId = Uuid::uuid4()->toString();

        $eventStore = \HttpEventStore\Http\HttpEventStore::create('localhost', '2113');
        $event1     = new \HttpEventStore\WritableEvent('productWasAddedToBasket', ['productId' => 'product1', 'name' => 'Teapot']);
        $event2     = new \HttpEventStore\WritableEvent('productWasRemovedFromBasket', ['productId' => 'product1']);
        $eventStore->writeStream($streamId, [$event1, $event2]);

        $events = $eventStore->readStream($streamId);

        $this->assertEquals([$event1, $event2], $events);
    }

    /** @test */
    public function deleting_a_stream()
    {
        $streamId = Uuid::uuid4()->toString();

        $eventStore = \HttpEventStore\Http\HttpEventStore::create('localhost', '2113');
        $event      = new \HttpEventStore\WritableEvent('productWasAddedToBasket', ['productId' => 'product1', 'name' => 'Teapot']);
        $eventStore->writeStream($streamId, [$event]);

        $eventStore->deleteStream($streamId);

        $this->assertThatStreamDoesNotExist($streamId);
    }

    /** @test */
    public function custom_events()
    {
        $streamId = Uuid::uuid4()->toString();

        $eventStore = \HttpEventStore\Http\HttpEventStore::create('localhost', '2113');
        $event1     = new \tests\HttpEventStore\fixtures\CustomEvent('productWasAddedToBasket', ['productId' => 'product1', 'name' => 'Teapot']);
        $event2     = new \tests\HttpEventStore\fixtures\CustomEvent('productWasRemovedFromBasket', ['productId' => 'product1']);

        $eventStore->writeStream($streamId, [$event1, $event2]);

        $this->assertCount(2, $eventStore->readStream($streamId));
    }
}
