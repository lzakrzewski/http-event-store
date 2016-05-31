<?php

namespace tests\HttpEventStoreClient;

use HttpEventStoreClient\HttpClient;

abstract class EventStoreTestCase extends \PHPUnit_Framework_TestCase
{
    /** @var HttpClient */
    protected $client;
    
    /** {@inheritdoc} */
    protected function setUp()
    {
        parent::setUp();

        $this->client = new HttpClient();
    }

    /** {@inheritdoc} */
    protected function tearDown()
    {
        $this->client = null;

        parent::tearDown();
    }
}
