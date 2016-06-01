<?php

namespace tests\HttpEventStore;

use GuzzleHttp\Client as Guzzle;
use HttpEventStore\Exception\StreamDoesNotExist;
use HttpEventStore\Http\HttpEventStore;

abstract class EventStoreTestCase extends \PHPUnit_Framework_TestCase
{
    /** @var HttpEventStore */
    protected $eventStore;
    
    /** @var Guzzle */
    protected $guzzle;

    protected function given(array $events)
    {
        foreach (array_keys($events) as $streamId) {
            $this->eventStore->writeStream($streamId, $events[$streamId]);
        }
    }

    protected function givenEventStoreFailed()
    {
        $this->eventStore = new HttpEventStore($this->guzzle, '128.0.0.1', '2113');
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

    /** {@inheritdoc} */
    protected function setUp()
    {
        parent::setUp();

        $this->guzzle = new Guzzle();
        $this->eventStore = new HttpEventStore($this->guzzle, '127.0.0.1', '2113');
    }

    /** {@inheritdoc} */
    protected function tearDown()
    {
        $this->guzzle = null;
        $this->eventStore = null;

        parent::tearDown();
    }
}
