<?php

namespace tests\HttpEventStore\Http;

use GuzzleHttp\ClientInterface as GuzzleInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use HttpEventStore\Http\HttpClient;

class HttpClientTest extends \PHPUnit_Framework_TestCase
{
    /** @var GuzzleInterface */
    private $guzzle;

    /** @var HttpClient */
    private $client;

    /** @test */
    public function it_can_read_http_api_with_event_store_format()
    {
        $this->guzzle->request(
            'GET',
            'localhost:4333/some-endpoint',
            [
                'headers' => ['Accept' => ['application/vnd.eventstore.events+json']],
            ]
        )->willReturn(new Response(200, [], '{"count": 0}'));

        $result = $this->client->request(HttpClient::METHOD_GET, 'some-endpoint');

        $this->assertEquals(['count' => 0], $result);
    }

    /** @test */
    public function it_can_read_http_api_with_projection_format()
    {
        $this->guzzle->request(
            'GET',
            'localhost:4333/some-endpoint',
            [
                'headers' => ['Accept' => ['application/json']],
            ]
        )->willReturn(new Response(200, [], '{"count": 0}'));

        $result = $this->client->request(HttpClient::METHOD_GET, 'some-endpoint', null, HttpClient::FORMAT_PROJECTION);

        $this->assertEquals(['count' => 0], $result);
    }

    /** @test */
    public function it_can_write_http_api_with_event_store_format()
    {
        $this->guzzle->request(
            'POST',
            'localhost:4333/some-endpoint',
            [
                'headers' => ['Content-Type' => ['application/vnd.eventstore.events+json']],
            ]
        )->willReturn(new Response(201, [], ''));

        $this->client->request(HttpClient::METHOD_POST, 'some-endpoint');
    }

    /** @test */
    public function it_can_write_http_api_with_event_store_format_and_body()
    {
        $this->guzzle->request(
            'POST',
            'localhost:4333/some-endpoint',
            [
                'headers' => ['Content-Type' => ['application/vnd.eventstore.events+json']],
                'body'    => '{"some": "data"}',
            ]
        )->willReturn(new Response(201, [], ''));

        $this->client->request(HttpClient::METHOD_POST, 'some-endpoint', '{"some": "data"}');
    }

    /** @test */
    public function it_can_write_http_api_with_projection_format()
    {
        $this->guzzle->request(
            'POST',
            'localhost:4333/some-endpoint',
            [
                'headers' => ['Content-Type' => ['application/json']],
                'auth'    => ['user', 'password'],
            ]
        )->willReturn(new Response(201, [], ''));

        $this->client->request(HttpClient::METHOD_POST, 'some-endpoint', null, HttpClient::FORMAT_PROJECTION);
    }

    /** @test */
    public function it_can_write_http_api_with_projection_format_and_body()
    {
        $this->guzzle->request(
            'POST',
            'localhost:4333/some-endpoint',
            [
                'headers' => ['Content-Type' => ['application/json']],
                'body'    => '{"some": "query"}',
                'auth'    => ['user', 'password'],
            ]
        )->willReturn(new Response(201, [], ''));

        $this->client->request(HttpClient::METHOD_POST, 'some-endpoint', '{"some": "query"}', HttpClient::FORMAT_PROJECTION);
    }

    /** @test */
    public function it_can_delete_resource_with_event_store_format()
    {
        $this->guzzle->request(
            'DELETE',
            'localhost:4333/some-endpoint',
            []
        )->willReturn(new Response(204, [], ''));

        $this->client->request(HttpClient::METHOD_DELETE, 'some-endpoint');
    }

    /** @test */
    public function it_can_delete_resource_with_projection_format()
    {
        $this->guzzle->request(
            'DELETE',
            'localhost:4333/some-endpoint',
            []
        )->willReturn(new Response(204, [], ''));

        $this->client->request(HttpClient::METHOD_DELETE, 'some-endpoint', null, HttpClient::FORMAT_PROJECTION);
    }

    /** @test */
    public function it_fails_when_request_method_is_invalid()
    {
        $this->expectException(\RuntimeException::class);

        $this->client->request('PUT', 'some-endpoint');
    }

    /** @test */
    public function it_can_read_event_store_in_bath()
    {
        $expectedRequest1 = new Request('GET', 'http://some-absolute/uri1', ['Accept' => ['application/vnd.eventstore.atom+json']]);
        $expectedRequest2 = new Request('GET', 'http://some-absolute/uri2', ['Accept' => ['application/vnd.eventstore.atom+json']]);

        $this->guzzle
            ->sendAsync($expectedRequest1, [])
            ->willReturn(
                new Response(200, [], '{"some": "data1"}')
            );

        $this->guzzle
            ->sendAsync($expectedRequest2, [])
            ->willReturn(
                new Response(200, [], '{"some": "data2"}')
            );

        $result = $this->client->requestsToAbsoluteUriInBatch(
            HttpClient::METHOD_GET,
            [
                'http://some-absolute/uri1',
                'http://some-absolute/uri2',
            ]
        );

        $this->assertEquals([['some' => 'data1'], ['some' => 'data2']], $result);
    }

    /** @test */
    public function it_fails_when_try_to_send_requests_in_bath_with_wrong_method()
    {
        $this->expectException(\RuntimeException::class);

        $expectedRequest1 = new Request('GET', 'http://some-absolute/uri1', ['Accept' => ['application/vnd.eventstore.atom+json']]);

        $this->guzzle
            ->sendAsync($expectedRequest1, [])
            ->willReturn(
                new Response(200, [], '{"some": "data1"}')
            );

        $this->client->requestsToAbsoluteUriInBatch(
            'PUT',
            [
                'http://some-absolute/uri1',
            ]
        );
    }

    /** {@inheritdoc} */
    protected function setUp()
    {
        $this->guzzle = $this->prophesize(GuzzleInterface::class);
        $this->client = new HttpClient($this->guzzle->reveal(), 'localhost', '4333', 'user', 'password');
    }

    /** {@inheritdoc} */
    protected function tearDown()
    {
        $this->guzzle = null;
        $this->client = null;
    }
}
