# Projection

**Notice:** There is no official support for projections under EventStore `HTTP API`, but docs are still available [Projections](https://github.com/EventStore/EventStore/wiki/Projections).

## Interface
```php
interface Projection
{
    /**
     * @param $projectionId
     * @param $query
     *
     * @throws EventStoreException
     */
    public function createProjection($projectionId, $query);

    /**
     * @param $projectionId
     *
     * @throws EventStoreException
     *
     * @return array|null
     */
    public function readProjection($projectionId);
}
```

## Object creation

#### With factory method
```php
$projection = \HttpEventStore\Http\HttpProjection::create('127.0.0.1', '2113', 'admin', 'changeit');
```

#### With dependency injection
```php
$guzzle     = new \GuzzleHttp\Client();
$httpClient = new \HttpEventStore\Http\HttpClient($guzzle, 'localhost', '2113', 'admin', 'changeit');
$projection = new \HttpEventStore\Http\HttpProjection($httpClient);
```

## Usage
```php
        $streamId = Uuid::uuid4()->toString();

        $eventStore = \HttpEventStore\Http\HttpEventStore::create('127.0.0.1', '2113');
        $event1     = new \HttpEventStore\WritableEvent('productWasAddedToBasket', ['productId' => 'product1', 'name' => 'Teapot']);
        $event2     = new \HttpEventStore\WritableEvent('productWasRemovedFromBasket', ['productId' => 'product1']);

        // Writing to a Stream
        $eventStore->writeStream($streamId, [$event1, $event2]);

        // Creating a projection
        $projection = \HttpEventStore\Http\HttpProjection::create('127.0.0.1', '2113', 'admin', 'changeit');

        $countOfEventsQuery = <<<STR
fromStream('$streamId').
    when({
       \$init : function(s,e) {return {count : 0}},
       \$any  : function(s,e) {return {count : s.count +1}}
    })
;
STR;
        $projectionId = 'projection-' . $streamId;
        $projection->createProjection($projectionId, $countOfEventsQuery);

        // Reading of a projection
        $countOfEvents = $projection->readProjection($streamId);

        // Your logic with count of events
```