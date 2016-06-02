<?php

namespace tests\HttpEventStore;

use GuzzleHttp\Client as Guzzle;
use HttpEventStore\Exception\StreamDoesNotExist;
use HttpEventStore\Http\HttpClient;
use HttpEventStore\Http\HttpEventStore;
use HttpEventStore\Http\HttpProjection;

abstract class EventStoreTestCase extends \PHPUnit_Framework_TestCase
{
    /** @var HttpEventStore */
    protected $eventStore;

    /** @var HttpProjection */
    protected $projection;

    /** @var HttpClient */
    private $client;

    protected function given(array $events)
    {
        foreach (array_keys($events) as $streamId) {
            $this->eventStore->writeStream($streamId, $events[$streamId]);
        }
    }

    protected function givenEventStoreFailed()
    {
        $this->client     = new HttpClient(new Guzzle(), '128.0.0.1', '2113', 'admin', 'changeit');
        $this->eventStore = new HttpEventStore($this->client);
        $this->projection = new HttpProjection($this->client);
    }

    protected function assertThatStreamContainsEvents(array $events, $streamId)
    {
        $this->assertEquals($events, $this->eventStore->readStream($streamId));
    }

    protected function assertThatStreamDoesNotExist($streamId)
    {
        $exception = null;

        try {
            $this->eventStore->readStream($streamId);
        } catch (\Exception $exception) {
        }

        $this->assertInstanceOf(StreamDoesNotExist::class, $exception);
    }

    protected function assertThatProjectionExists($projectionId)
    {
        $this->assertNotNull($this->projection->readProjection($projectionId));
    }

    /** {@inheritdoc} */
    protected function setUp()
    {
        parent::setUp();

        $this->client     = new HttpClient(new Guzzle(), '127.0.0.1', '2113', 'admin', 'changeit');
        $this->eventStore = new HttpEventStore($this->client);
        $this->projection = new HttpProjection($this->client);
    }

    /** {@inheritdoc} */
    protected function tearDown()
    {
        $this->client     = null;
        $this->eventStore = null;
        $this->projection = null;

        parent::tearDown();
    }
}
