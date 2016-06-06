# HttpEventStoreClient
[![Build Status](https://travis-ci.org/lzakrzewski/http-event-store.svg?branch=master)](https://travis-ci.org/lzakrzewski/http-event-store) [![Latest Unstable Version](https://poser.pugx.org/lzakrzewski/http-event-store/v/unstable)](https://packagist.org/packages/lzakrzewski/http-event-store) [![Total Downloads](https://poser.pugx.org/lzakrzewski/http-event-store/downloads)](https://packagist.org/packages/lzakrzewski/http-event-store)

Client for communication with Event Store `HTTP API`.
Read more about Event Store `HTTP API` [http://docs.geteventstore.com/http-api/latest](http://docs.geteventstore.com/http-api/latest).

This library is independent part of [es-sandbox](https://github.com/lzakrzewski/es-sandbox).

Requirements
------------
```json
  "require": {
    "php": ">=5.6",
    "guzzlehttp/guzzle": "~6.0",
    "ramsey/uuid" : "~3.0"
  }
```

Installation
--------
Require the library with composer:

```sh
composer require lzakrzewski/http-event-store
```

Usage
--------
- [EventStore](doc/event-store.md)
- [Projection](doc/projection.md)

Example
--------
```php
$streamId = Uuid::uuid4()->toString();

$eventStore = \HttpEventStore\Http\HttpEventStore::create('127.0.0.1', '2113');
$event1     = new \HttpEventStore\WritableEvent('productWasAddedToBasket', ['productId' => 'product1', 'name' => 'Teapot']);
$event2     = new \HttpEventStore\WritableEvent('productWasRemovedFromBasket', ['productId' => 'product1']);

// Writing to a Stream
$eventStore->writeStream($streamId, [$event1, $event2]);

// Reading from a Stream
$events = $eventStore->readStream($streamId);

 // Your logic with events there...
```
