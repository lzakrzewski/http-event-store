# Event store usage

## Interface
```php
interface EventStore
{
    const STREAM_DOES_NOT_EXIST = 404;
    const REQUEST_BODY_INVALID  = 400;

    /**
     * @param $streamId
     *
     * @throws EventStoreException
     *
     * @return string
     */
    public function readStream($streamId);

    /**
     * @param $streamId
     * @param WritableToStream[] $events
     *
     * @throws EventStoreException
     */
    public function writeStream($streamId, array $events);

    /**
     * @param $streamId
     *
     * @throws EventStoreException
     */
    public function deleteStream($streamId);
}
```

## Object creation

#### With factory method
```php
$eventStore = \HttpEventStore\Http\HttpEventStore::create('localhost', '2113');
```

#### With dependency injection
```php
$guzzle = new \GuzzleHttp\Client();
$httpClient = new \HttpEventStore\Http\HttpClient($guzzle, 'localhost', '2113');
$eventStore = new \HttpEventStore\Http\HttpEventStore($httpClient);
```

## Writing to a Stream
```php
$streamId = Uuid::uuid4()->toString();

$eventStore = \HttpEventStore\Http\HttpEventStore::create('localhost', '2113');
$event1     = new \HttpEventStore\WritableEvent('productWasAddedToBasket', ['productId' => 'product1', 'name' => 'Teapot']);
$event2     = new \HttpEventStore\WritableEvent('productWasRemovedFromBasket', ['productId' => 'product1']);

$eventStore->writeStream($streamId, [$event1, $event2]);
```

## Reading from a Stream
```php
$streamId = Uuid::uuid4()->toString();

$eventStore = \HttpEventStore\Http\HttpEventStore::create('localhost', '2113');
$event1     = new \HttpEventStore\WritableEvent('productWasAddedToBasket', ['productId' => 'product1', 'name' => 'Teapot']);
$event2     = new \HttpEventStore\WritableEvent('productWasRemovedFromBasket', ['productId' => 'product1']);
$eventStore->writeStream($streamId, [$event1, $event2]);

$events = $eventStore->readStream($streamId);
```

## Deleting a Stream
```php
$streamId = Uuid::uuid4()->toString();

$eventStore = \HttpEventStore\Http\HttpEventStore::create('localhost', '2113');
$event      = new \HttpEventStore\WritableEvent('productWasAddedToBasket', ['productId' => 'product1', 'name' => 'Teapot']);
$eventStore->writeStream($streamId, [$event]);

$eventStore->deleteStream($streamId);
```

## Custom events
`http-event-store` allow's you to write a Stream with custom events. Object of your custom event should implement `\HttpEventStore\WritableToStream`.

Example:

```php
//...

use HttpEventStore\WritableToStream;

final class CustomEvent implements WritableToStream
{
    /** @var array */
    private $data;

    /** @var string */
    private $type;

    /**
     * @param string $type
     * @param array  $data
     */
    public function __construct($type, array $data)
    {
        $this->type = $type;
        $this->data = $data;
    }

    /** {@inheritdoc} */
    public function type()
    {
        return $this->type;
    }

    /** {@inheritdoc} */
    public function data()
    {
        return $this->data;
    }
}

```

Currently hydration of custom events during reading from a Stream is not supported.
Each event read from the stream is an instance of `\HttpEventStore\WritableEvent`.