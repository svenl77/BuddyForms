<?php

namespace tk\Psr\Http\Client;

use tk\Psr\Http\Message\RequestInterface;
use tk\Psr\Http\Message\ResponseInterface;
interface ClientInterface
{
    /**
     * Sends a PSR-7 request and returns a PSR-7 response.
     *
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     *
     * @throws \Psr\Http\Client\ClientExceptionInterface If an error happens while processing the request.
     */
    public function sendRequest(\tk\Psr\Http\Message\RequestInterface $request) : \tk\Psr\Http\Message\ResponseInterface;
}
