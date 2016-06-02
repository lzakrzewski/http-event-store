<?php

namespace tests\HttpEventStore;

use GuzzleHttp\Client as Guzzle;
use HttpEventStore\Exception\StreamDoesNotExist;
use HttpEventStore\Http\HttpClient;
use HttpEventStore\Http\HttpEventStore;
use HttpEventStore\Http\HttpProjection;

//Todo: Add factory methods
abstract class EventStoreTestCase extends \PHPUnit_Framework_TestCase
{
    /** @var Guzzle */
    protected $guzzle;

    /** @var HttpEventStore */
    protected $eventStore;

    /** @var HttpProjection */
    protected $projection;

    protected function given(array $events)
    {
        foreach (array_keys($events) as $streamId) {
            $this->eventStore->writeStream($streamId, $events[$streamId]);
        }
    }

    protected function givenEventStoreFailed()
    {
        $this->eventStore = new HttpEventStore(new HttpClient($this->guzzle, '128.0.0.1', '2113'));
        $this->projection = new HttpProjection($this->guzzle, '128.0.0.1', '2113', 'admin', 'changeit');
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

        $this->guzzle     = new Guzzle();
        $this->eventStore = new HttpEventStore(new HttpClient($this->guzzle, '127.0.0.1', '2113'));
        $this->projection = new HttpProjection($this->guzzle, '127.0.0.1', '2113', 'admin', 'changeit');
    }

    /** {@inheritdoc} */
    protected function tearDown()
    {
        $this->guzzle     = null;
        $this->eventStore = null;
        $this->projection = null;

        parent::tearDown();
    }
}
