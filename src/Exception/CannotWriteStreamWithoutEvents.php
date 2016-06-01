<?php

namespace HttpEventStore\Exception;

class CannotWriteStreamWithoutEvents extends \RuntimeException implements EventStoreException
{
}
