<?php

namespace CarParts\Kernel\Contracts;

use Psr\Http\Message\RequestInterface;

/**
 * Interface AuthorizerAccessToken.
 */
interface AccessTokenInterface
{
    /**
     * @return array
     */
    public function getToken();

    /**
     * @return \CarParts\Kernel\Contracts\AccessTokenInterface
     */
    public function refresh();

    /**
     * @param \Psr\Http\Message\RequestInterface $request
     * @param array                              $requestOptions
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    public function applyToRequest(RequestInterface $request, array $requestOptions = []);
}
