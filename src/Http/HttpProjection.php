<?php

namespace HttpEventStore\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use HttpEventStore\Exception\EventStoreConnectionFailed;
use HttpEventStore\Projection;

class HttpProjection implements Projection
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
     * @param $username
     * @param $password
     *
     * @return HttpProjection
     */
    public static function create($host, $port, $username, $password)
    {
        return new self(new HttpClient(new Client(), $host, $port, $username, $password));
    }

    /** {@inheritdoc} */
    public function createProjection($projectionId, $query)
    {
        try {
            $this->client->request(
                HttpClient::METHOD_POST,
                $this->oneTimeProjectionUri($projectionId),
                $query,
                HttpClient::FORMAT_PROJECTION
            );
        } catch (RequestException $e) {
            throw new EventStoreConnectionFailed($e->getMessage());
        }
    }

    /** {@inheritdoc} */
    public function readProjection($projectionId)
    {
        try {
            $result = $this->client
                ->request(
                    HttpClient::METHOD_GET,
                    $this->projectionResultUri($projectionId),
                    null,
                    HttpClient::FORMAT_PROJECTION
                );

            if (empty($result)) {
                return;
            }

            return $result;
        } catch (RequestException $e) {
            throw new EventStoreConnectionFailed($e->getMessage());
        }
    }

    private function oneTimeProjectionUri($projectionId)
    {
        return sprintf('projections/onetime?name=%s&enabled=yes', $projectionId);
    }

    private function projectionResultUri($projectionId)
    {
        return sprintf('projection/%s/result', $projectionId);
    }
}
