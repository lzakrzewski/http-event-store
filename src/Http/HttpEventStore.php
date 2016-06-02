<?php

namespace HttpEventStore\Http;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\ClientInterface as GuzzleInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use HttpEventStore\EventStore;
use HttpEventStore\Exception\CannotWriteStreamWithoutEvents;
use HttpEventStore\Exception\EventStoreConnectionFailed;
use HttpEventStore\Exception\StreamDoesNotExist;
use HttpEventStore\WritableEvent;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;

class HttpEventStore implements EventStore
{
    const STREAM_DOES_NOT_EXIST = 404;
    const REQUEST_BODY_INVALID  = 400;

    /** @var GuzzleInterface */
    private $guzzle;

    /** @var string */
    private $uri;

    /**
     * @param Guzzle $guzzle
     * @param string $host
     * @param string $port
     */
    public function __construct(Guzzle $guzzle, $host, $port)
    {
        $this->guzzle = $guzzle;
        $this->uri    = sprintf('%s:%s', $host, $port);
    }

    /**
     * @param $host
     * @param $port
     *
     * @return HttpEventStore
     */
    public static function create($host, $port)
    {
        return new self(new Guzzle(), $host, $port);
    }

    /** {@inheritdoc} */
    public function readStream($streamId)
    {
        try {
            $response = $this->guzzle->request('GET', $this->streamUri($streamId), [
                'headers' => ['Accept' => ['application/vnd.eventstore.events+json']],
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

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
            $this->guzzle->request('POST', $this->streamUri($streamId), [
                'headers' => ['Content-Type' => ['application/vnd.eventstore.events+json']],
                'body'    => $this->serialize($events),
            ]);
        } catch (RequestException $e) {
            $this->handleException($e);
        }
    }

    /** {@inheritdoc} */
    public function deleteStream($streamId)
    {
        try {
            $this->guzzle->request('DELETE', $this->streamUri($streamId), ['headers' => ['Content-Type' => ['application/vnd.eventstore.events+json']]]);
        } catch (RequestException $e) {
            $this->handleException($e);
        }
    }

    private function readEvents(array $eventUris)
    {
        $requests = array_map(
            function ($eventUri) {
                return new Request('GET', $eventUri, ['Accept' => ['application/vnd.eventstore.atom+json']]);
            },
            $eventUris
        );

        $responses = Pool::batch($this->guzzle, $requests);

        return array_reverse(array_map(function (ResponseInterface $response) {
            $contents = json_decode($response->getBody()->getContents(), true);

            return new WritableEvent(
                $contents['content']['eventType'],
                $contents['content']['data']
            );
        }, $responses));
    }

    private function streamUri($streamId)
    {
        return sprintf('%s/streams/%s', $this->uri, $streamId);
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
