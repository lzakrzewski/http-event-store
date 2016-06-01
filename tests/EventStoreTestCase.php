<?php

namespace tests\HttpEventStore;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
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
        $this->eventStore = new HttpEventStore($this->guzzle, 'localhost', '21131111');
    }

    /** {@inheritdoc} */
    protected function setUp()
    {
        parent::setUp();

        $this->guzzle = new Guzzle();
        $this->eventStore = new HttpEventStore($this->guzzle, 'localhost', '2113');
    }

    /** {@inheritdoc} */
    protected function tearDown()
    {
        $this->guzzle = null;
        $this->eventStore = null;

        parent::tearDown();
    }
}
