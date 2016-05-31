<?php

namespace HttpEventStoreClient;

use GuzzleHttp\ClientInterface as GuzzleInterface;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\RequestException;

class HttpClient implements Client
{
    /** @var GuzzleInterface */
    private $guzzle;

    /** @var string */
    private $uri;

    /** @var string */
    private $auth;

    /**
     * @param Guzzle $guzzle
     * @param string $host
     * @param string $port
     * @param string|null $username
     * @param string|null $password
     */
    public function __construct(Guzzle $guzzle, $host, $port, $username = null, $password = null)
    {
        $this->guzzle = $guzzle;
        $this->uri = sprintf("%s:%s", $host, $port);
        $this->auth = [$username, $password];
    }

    /**
     * @param $host
     * @param $port
     * @param string|null $username
     * @param string|null $password
     *
     * @return HttpClient
     */
    public static function create($host, $port, $username = null, $password = null)
    {
        return new self(new Guzzle(), $host, $port, $username, $password);
    }

    /** {@inheritdoc} */
    public function readStream($streamId)
    {
        return 'contents';
        
        try {
            $response = $this->guzzle->request('GET', $this->streamUri($streamId), [
                'headers' => ['Accept' => ['application/vnd.eventstore.events+json']],
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return [];
        }
    }

    /** {@inheritdoc} */
    public function writeStream($streamId, $contents)
    {
        try {
            $this->guzzle->request('POST', $this->streamUri($streamId), [
                'headers' => ['Content-Type' => ['application/vnd.eventstore.events+json']],
                'body'    => $contents,
            ]);
        } catch (RequestException $e) {
            throw new EventStoreException($e->getMessage());
        }
    }

    /** {@inheritdoc} */
    public function readEvent($eventUri)
    {
    }

    /** {@inheritdoc} */
    public function deleteStream($streamId)
    {
    }

    /** {@inheritdoc} */
    public function createProjection($projectionId, $query)
    {
    }

    /** {@inheritdoc} */
    public function readProjection($projectionId)
    {
    }

    private function streamUri($streamId)
    {
        return sprintf('%s/streams/%s', $this->uri, $streamId);
    }
}
