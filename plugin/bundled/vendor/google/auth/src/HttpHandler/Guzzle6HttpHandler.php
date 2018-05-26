<?php

namespace Sgdg\Vendor\Google\Auth\HttpHandler;

use Sgdg\Vendor\GuzzleHttp\ClientInterface;
use Sgdg\Vendor\Psr\Http\Message\RequestInterface;
use Sgdg\Vendor\Psr\Http\Message\ResponseInterface;

class Guzzle6HttpHandler
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Accepts a PSR-7 request and an array of options and returns a PSR-7 response.
     *
     * @param RequestInterface $request
     * @param array $options
     *
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, array $options = [])
    {
        return $this->client->send($request, $options);
    }

    /**
     * Accepts a PSR-7 request and an array of options and returns a PromiseInterface
     *
     * @param RequestInterface $request
     * @param array $options
     *
     * @return \Sgdg\Vendor\GuzzleHttp\Promise\Promise
     */
    public function async(RequestInterface $request, array $options = [])
    {
        return $this->client->sendAsync($request, $options);
    }
}
