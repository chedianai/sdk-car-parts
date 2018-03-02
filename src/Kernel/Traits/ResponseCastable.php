<?php

namespace CarParts\Kernel\Traits;

use CarParts\Kernel\Contracts\Arrayable;
use CarParts\Kernel\Contracts\ResponseFormatted;
use CarParts\Kernel\Exceptions\InvalidArgumentException;
use CarParts\Kernel\Exceptions\InvalidConfigException;
use CarParts\Kernel\Http\Response;
use CarParts\Kernel\Support\Collection;
use Psr\Http\Message\ResponseInterface;

/**
 * Trait ResponseCastable.
 */
trait ResponseCastable
{
    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param string|null                         $type
     *
     * @return array|\CarParts\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     *
     * @throws \CarParts\Kernel\Exceptions\InvalidConfigException
     */
    protected function castResponseToType(ResponseInterface $response, $type = null)
    {
        $response = Response::buildFromPsrResponse($response);
        $response->getBody()->rewind();

        switch ($type ? $type : 'array') {
            case 'collection':
                return $response->toCollection();
            case 'array':
                return $response->toArray();
            case 'object':
                return $response->toObject();
            case 'raw':
                return $response;
            default:
                if (!is_subclass_of($type, Arrayable::class) || !is_subclass_of($type, ResponseFormatted::class)) {
                    throw new InvalidConfigException(sprintf(
                        'Config key "response_type" classname must be an instanceof %s and %s',
                        Arrayable::class, ResponseFormatted::class
                    ));
                }

                return (new $type($response))->format();
        }
    }

    /**
     * @param mixed       $response
     * @param string|null $type
     *
     * @return array|\CarParts\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     *
     * @throws \CarParts\Kernel\Exceptions\InvalidArgumentException
     * @throws \CarParts\Kernel\Exceptions\InvalidConfigException
     */
    protected function detectAndCastResponseToType($response, $type = null)
    {
        switch (true) {
            case $response instanceof ResponseInterface:
                $response = Response::buildFromPsrResponse($response);

                break;
            case ($response instanceof Collection) || is_array($response) || is_object($response):
                $response = new Response(200, [], json_encode($response));

                break;
            case is_scalar($response):
                $response = new Response(200, [], $response);

                break;
            default:
                throw new InvalidArgumentException(sprintf('Unsupported response type "%s"', gettype($response)));
        }

        return $this->castResponseToType($response, $type);
    }
}
