<?php

namespace tests\HttpEventStore;

use GuzzleHttp\Client as Guzzle;
use HttpEventStore\Http\HttpEventStore;

abstract class EventStoreTestCase extends \PHPUnit_Framework_TestCase
{
    /** @var HttpEventStore */
    protected $eventStore;
    
    /** @var HttpEventStore */
    protected $guzzle;
    
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
