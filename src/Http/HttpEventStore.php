<?php

namespace HttpEventStore\Http;

use GuzzleHttp\Exception\RequestException;
use HttpEventStore\EventStore;
use HttpEventStore\Exception\CannotWriteStreamWithoutEvents;
use HttpEventStore\Exception\EventStoreConnectionFailed;
use HttpEventStore\Exception\StreamDoesNotExist;
use HttpEventStore\WritableEvent;
use Ramsey\Uuid\Uuid;

class HttpEventStore implements EventStore
{
    /** @var HttpClient */
    private $client;

    /**
     * @param HttpClient $client
     */
    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    /** {@inheritdoc} */
    public function readStream($streamId)
    {
        try {
            $result = $this->client->request(HttpClient::METHOD_GET, $this->streamUri($streamId));

            if (!isset($result['entries'])) {
                return [];
            }

            return $this->readEvents(
                array_map(
                    function (array $entry) {
                        return $entry['id'];
                    },
                    $result['entries']
                )
            );
        } catch (RequestException $e) {
            $this->handleException($e);
        }
    }

    /** {@inheritdoc} */
    public function writeStream($streamId, array $events)
    {
        try {
            $this->client->request(HttpClient::METHOD_POST, $this->streamUri($streamId), $this->serialize($events));
        } catch (RequestException $e) {
            $this->handleException($e);
        }
    }

    /** {@inheritdoc} */
    public function deleteStream($streamId)
    {
        try {
            $this->client->request(HttpClient::METHOD_DELETE, $this->streamUri($streamId));
        } catch (RequestException $e) {
            $this->handleException($e);
        }
    }

    private function readEvents(array $eventUris)
    {
        $results = $this->client->requestsToAbsoluteUriInBatch(HttpClient::METHOD_GET, $eventUris);

        return array_reverse(array_map(function (array $result) {
            return new WritableEvent(
                $result['content']['eventType'],
                $result['content']['data']
            );
        }, $results));
    }

    private function streamUri($streamId)
    {
        return sprintf('streams/%s', $streamId);
    }

    private function serialize(array $events)
    {
        $data = [];

        foreach ($events as $event) {
            $data[] = [
                'eventId'   => Uuid::uuid4()->toString(),
                'eventType' => $event->type(),
                'data'      => $event->data(),
            ];
        }

        return json_encode($data);
    }

    private function handleException(RequestException $exception)
    {
        if ($exception->getCode() === self::STREAM_DOES_NOT_EXIST) {
            throw new StreamDoesNotExist($exception->getMessage());
        }

        if ($exception->getCode() === self::REQUEST_BODY_INVALID  && empty(json_decode($exception->getRequest()->getBody()))) {
            throw new CannotWriteStreamWithoutEvents($exception->getMessage());
        }

        throw new EventStoreConnectionFailed($exception->getMessage());
    }
}
