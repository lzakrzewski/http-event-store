<?php

namespace HttpEventStore\Http;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\ClientInterface as GuzzleInterface;
use GuzzleHttp\Exception\RequestException;
use HttpEventStore\Exception\EventStoreConnectionFailed;
use HttpEventStore\Projection;

class HttpProjection implements Projection
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
     * @param string $username
     * @param string $password
     */
    public function __construct(Guzzle $guzzle, $host, $port, $username, $password)
    {
        $this->guzzle = $guzzle;
        $this->uri    = sprintf('%s:%s', $host, $port);
        $this->auth   = [$username, $password];
    }

    /** {@inheritdoc} */
    public function createProjection($projectionId, $query)
    {
        try {
            $this->guzzle->request(
                'POST',
                sprintf('%s/projections/onetime?name=%s&enabled=yes', $this->uri, $projectionId),
                [
                    'headers' => [
                        'Content-Type' => ['application/json'],
                    ],
                    'body' => $query,
                    'auth' => $this->auth,
                ]
            );
        } catch (RequestException $e) {
            throw new EventStoreConnectionFailed($e->getMessage());
        }
    }

    /** {@inheritdoc} */
    public function readProjection($projectionId)
    {
        try {
            $response = $this->guzzle
                ->request(
                    'GET',
                    sprintf('%s/projection/%s/result', $this->uri, $projectionId),
                    [
                        'headers' => [
                            'Accept' => ['application/json'],
                        ],
                    ]
                );

            $result = (array) json_decode($response->getBody()->getContents(), true);

            if (empty($result)) {
                return;
            }

            return $result;
        } catch (RequestException $e) {
            throw new EventStoreConnectionFailed($e->getMessage());
        }
    }
}
