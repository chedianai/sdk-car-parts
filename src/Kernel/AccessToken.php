<?php

namespace CarParts\Kernel;

use CarParts\Kernel\Contracts\AccessTokenInterface;
use CarParts\Kernel\Exceptions\HttpException;
use CarParts\Kernel\Exceptions\InvalidArgumentException;
use CarParts\Kernel\Traits\HasHttpRequests;
use CarParts\Kernel\Traits\InteractsWithCache;
use Pimple\Container;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class AccessToken.
 */
class AccessToken implements AccessTokenInterface
{
    use HasHttpRequests, InteractsWithCache;

    /**
     * @var \Pimple\Container
     */
    protected $app;

    /**
     * @var string
     */
    protected $requestMethod = 'POST';

    /**
     * @var string
     */
    protected $endpointToGetToken = 'oauth/token';

    /**
     * @var string
     */
    protected $queryName;

    /**
     * @var array
     */
    protected $token;

    /**
     * @var int
     */
    protected $safeSeconds = 500;

    /**
     * @var string
     */
    protected $tokenKey = 'access_token';

    /**
     * @var string
     */
    protected $cachePrefix = 'car-parts.access_token.';

    /**
     * AccessToken constructor.
     *
     * @param \Pimple\Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * @return array
     *
     * @throws \CarParts\Kernel\Exceptions\HttpException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \CarParts\Kernel\Exceptions\InvalidConfigException
     * @throws \CarParts\Kernel\Exceptions\InvalidArgumentException
     */
    public function getRefreshedToken()
    {
        return $this->getToken(true);
    }

    /**
     * @param bool $refresh
     *
     * @return array
     *
     * @throws \CarParts\Kernel\Exceptions\HttpException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \CarParts\Kernel\Exceptions\InvalidConfigException
     * @throws \CarParts\Kernel\Exceptions\InvalidArgumentException
     */
    public function getToken($refresh = false)
    {
        $cacheKey = $this->getCacheKey();
        $cache = $this->getCache();

        if (!$refresh && $cache->has($cacheKey)) {
            return $cache->get($cacheKey);
        }

        $token = $this->requestToken($this->getCredentials(), true);

        $this->setToken($token[$this->tokenKey], $token['expires_in'] ? $token['expires_in'] : 7200);

        return $token;
    }

    /**
     * @param string $token
     * @param int    $lifetime
     *
     * @return \CarParts\Kernel\Contracts\AccessTokenInterface
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setToken($token, $lifetime = 7200)
    {
        $this->getCache()->set($this->getCacheKey(), [
            $this->tokenKey => $token,
            'expires_in' => $lifetime,
        ], $lifetime - $this->safeSeconds);

        return $this;
    }

    /**
     * @return \CarParts\Kernel\Contracts\AccessTokenInterface
     *
     * @throws \CarParts\Kernel\Exceptions\HttpException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \CarParts\Kernel\Exceptions\InvalidConfigException
     * @throws \CarParts\Kernel\Exceptions\InvalidArgumentException
     */
    public function refresh()
    {
        $this->getToken(true);

        return $this;
    }

    /**
     * @param array $credentials
     * @param bool  $toArray
     *
     * @return \Psr\Http\Message\ResponseInterface|\CarParts\Kernel\Support\Collection|array|object|string
     *
     * @throws \CarParts\Kernel\Exceptions\HttpException
     * @throws \CarParts\Kernel\Exceptions\InvalidConfigException
     * @throws \CarParts\Kernel\Exceptions\InvalidArgumentException
     */
    public function requestToken(array $credentials, $toArray = false)
    {
        $response = $this->sendRequest($credentials);
        $result = json_decode($response->getBody()->getContents(), true);
        $formatted = $this->castResponseToType($response, $this->app['config']->get('response_type'));

        if (empty($result[$this->tokenKey])) {
            throw new HttpException('Request access_token fail: '.json_encode($result, JSON_UNESCAPED_UNICODE), $response, $formatted);
        }

        return $toArray ? $result : $formatted;
    }

    /**
     * @param \Psr\Http\Message\RequestInterface $request
     * @param array                              $requestOptions
     *
     * @return \Psr\Http\Message\RequestInterface
     *
     * @throws \CarParts\Kernel\Exceptions\HttpException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \CarParts\Kernel\Exceptions\InvalidConfigException
     * @throws \CarParts\Kernel\Exceptions\InvalidArgumentException
     */
    public function applyToRequest(RequestInterface $request, array $requestOptions = [])
    {
        $query = $this->getQuery();

        if (isset($query['access_token'])) {
            $request = $request->withHeader('Authorization', 'Bearer ' . $query['access_token']);
        }

        return $request;
    }

    /**
     * Send http request.
     *
     * @param array $credentials
     *
     * @return ResponseInterface
     *
     * @throws \CarParts\Kernel\Exceptions\InvalidArgumentException
     */
    protected function sendRequest(array $credentials)
    {
        $options = [
            ('GET' === $this->requestMethod) ? 'query' : 'form_params' => $credentials,
        ];

        $this->baseUri = $this->app['config']['http']['base_uri'];

        return $this->setHttpClient($this->app['http_client'])->request($this->getEndpoint(), $this->requestMethod, $options);
    }

    /**
     * @return string
     */
    protected function getCacheKey()
    {
        return $this->cachePrefix.md5(json_encode($this->getCredentials()));
    }

    /**
     * The request query will be used to add to the request.
     *
     * @return array
     *
     * @throws \CarParts\Kernel\Exceptions\HttpException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \CarParts\Kernel\Exceptions\InvalidConfigException
     * @throws \CarParts\Kernel\Exceptions\InvalidArgumentException
     */
    protected function getQuery()
    {
        return [($this->queryName ? $this->queryName : $this->tokenKey) => $this->getToken()[$this->tokenKey]];
    }

    /**
     * @return string
     *
     * @throws \CarParts\Kernel\Exceptions\InvalidArgumentException
     */
    public function getEndpoint()
    {
        if (empty($this->endpointToGetToken)) {
            throw new InvalidArgumentException('No endpoint for access token request.');
        }

        return $this->endpointToGetToken;
    }

    /**
     * @return array
     */
    protected function getCredentials()
    {
        return [
            'grant_type'    => 'client_credentials',
            'client_id'     => $this->app['config']['client_id'],
            'client_secret' => $this->app['config']['client_secret'],
        ];
    }
}
