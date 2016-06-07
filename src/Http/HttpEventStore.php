<?php

namespace HttpEventStore\Http;

use GuzzleHttp\Client;
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

    /**
     * @param $host
     * @param $port
     *
     * @return HttpEventStore
     */
    public static function create($host, $port)
    {
        return new self(new HttpClient(new Client(), $host, $port));
    }

    /** {@inheritdoc} */
    public function readStream($streamId)
    {
        try {
            $eventUris = $this->eventUris($streamId);

            if (empty($eventUris)) {
                return [];
            }

            return $this->readEvents($eventUris);
        } catch (RequestException $e) {
            $this->handleException($e);
        }
    }

    private function eventUris($streamId)
    {
        $firstPage = $this
            ->client
            ->request(HttpClient::METHOD_GET, $this->streamUri($streamId));

        $nextPageUrl = $this->nextPageUrl($firstPage);

        return array_reverse(array_merge(
            $this->readEventUrisFromPageUrl($nextPageUrl),
            $this->readEventUrisFromPage($firstPage)
        ));
    }

    private function readEventUrisFromPage(array $page)
    {
        if (!isset($page['entries'])) {
            return [];
        }

        return array_reverse(array_map(function (array $entry) {
            return $entry['id'];
        }, $page['entries']));
    }

    private function readEventUrisFromPageUrl($pageUrl)
    {
        $page = $this
            ->client
            ->request(HttpClient::METHOD_GET, $pageUrl);

        $eventsFromNextPage = [];

        if (null === $page) {
            return [];
        }

        $nextPageUrl = $this->nextPageUrl($page);

        if (null !== $nextPageUrl) {
            $eventsFromNextPage = $this->readEventUrisFromPageUrl($nextPageUrl);
        }

        return array_merge($eventsFromNextPage, $this->readEventUrisFromPage($page));
    }

    private function nextPageUrl(array $page)
    {
        if (!isset($page['links'])) {
            return;
        }

        $nextPageLink = array_filter($page['links'], function ($link) {
            if (isset($link['relation']) && $link['relation'] == 'next') {
                return true;
            }
        });

        if (empty($nextPageLink)) {
            return;
        }

        return current($nextPageLink)['uri'];
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
        $results = $this->client->requestsBatch(HttpClient::METHOD_GET, $eventUris);

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
