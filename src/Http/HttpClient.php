<?php

namespace HttpEventStore\Http;

use GuzzleHttp\ClientInterface as GuzzleInterface;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

class HttpClient
{
    const METHOD_GET    = 'GET';
    const METHOD_POST   = 'POST';
    const METHOD_DELETE = 'DELETE';

    const FORMAT_EVENT_STORE      = 'application/vnd.eventstore.events+json';
    const FORMAT_EVENT_STORE_ATOM = 'application/vnd.eventstore.atom+json';
    const FORMAT_PROJECTION       = 'application/json';

    /** @var GuzzleInterface */
    private $guzzle;

    /** @var string */
    private $url;

    /** @var string */
    private $auth;

    /**
     * @param GuzzleInterface $guzzle
     * @param string          $host
     * @param string          $port
     * @param string|null     $username
     * @param string|null     $password
     */
    public function __construct(GuzzleInterface $guzzle, $host, $port, $username = null, $password = null)
    {
        $this->guzzle = $guzzle;
        $this->url    = sprintf('%s:%s', $host, $port);
        $this->auth   = [$username, $password];
    }

    public function request($method, $endpoint, $body = null, $format = self::FORMAT_EVENT_STORE)
    {
        $this->validateMethod($method);

        $response = $this->guzzle->request(
            $method,
            $this->uri($endpoint),
            $this->options($method, $body, $format)
        );

        return $this->decodeResponse($response);
    }

    public function requestsBatch($method, $uris)
    {
        $requests = array_map(
            function ($eventUri) use ($method) {
                $this->validateMethod($method);

                return new Request($method, $this->uri($eventUri), ['Accept' => [self::FORMAT_EVENT_STORE_ATOM]]);
            },
            $uris
        );

        $responses = Pool::batch($this->guzzle, $requests);

        return array_map(function (ResponseInterface $response) {
            return json_decode($response->getBody()->getContents(), true);
        }, $responses);
    }

    private function validateMethod($method)
    {
        $allowedMethods = [self::METHOD_GET, self::METHOD_POST, self::METHOD_DELETE];

        if (!in_array($method, $allowedMethods)) {
            throw new \RuntimeException(
                sprintf(
                    'Method: "%" is not allowed. Allowed methods: "%s"',
                    $method,
                    implode(', ', $allowedMethods)
                )
            );
        }
    }

    private function options($method, $body, $format)
    {
        $options = [];

        if ($method == self::METHOD_GET && $format == self::FORMAT_EVENT_STORE) {
            $options['headers']['Accept'] = [self::FORMAT_EVENT_STORE];
        }

        if ($method == self::METHOD_GET && $format == self::FORMAT_PROJECTION) {
            $options['headers']['Accept'] = [self::FORMAT_PROJECTION];
        }

        if ($method == self::METHOD_POST && $format == self::FORMAT_EVENT_STORE) {
            $options['headers']['Content-Type'] = [self::FORMAT_EVENT_STORE];
        }

        if ($method == self::METHOD_POST && $format == self::FORMAT_PROJECTION) {
            $options['headers']['Content-Type'] = [self::FORMAT_PROJECTION];
            $options['auth']                    = $this->auth;
        }

        if (null !== $body) {
            $options['body'] = $body;
        }

        return $options;
    }

    private function decodeResponse(ResponseInterface $response)
    {
        return json_decode($response->getBody()->getContents(), true);
    }

    private function uri($endpoint)
    {
        if (false !== strpos($endpoint, $this->url)) {
            return $endpoint;
        }

        return sprintf('%s/%s', $this->url, $endpoint);
    }
}
