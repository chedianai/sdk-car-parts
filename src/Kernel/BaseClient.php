<?php

namespace CarParts\Kernel;

use CarParts\Kernel\Contracts\AccessTokenInterface;
use CarParts\Kernel\Exceptions\AuthorizationException;
use CarParts\Kernel\Exceptions\ResourceNotFoundException;
use CarParts\Kernel\Exceptions\ServiceInvalidException;
use CarParts\Kernel\Exceptions\ValidationException;
use CarParts\Kernel\Http\Response;
use CarParts\Kernel\Traits\HasHttpRequests;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class BaseClient.
 */
class BaseClient
{
    use HasHttpRequests {
        request as performRequest;
    }

    /**
     * @var \CarParts\Kernel\ServiceContainer
     */
    protected $app;

    /**
     * @var \CarParts\Kernel\Contracts\AccessTokenInterface
     */
    protected $accessToken;

    /**
     * @var
     */
    protected $baseUri;

    /**
     * BaseClient constructor.
     *
     * @param \CarParts\Kernel\ServiceContainer                    $app
     * @param \CarParts\Kernel\Contracts\AccessTokenInterface|null $accessToken
     */
    public function __construct(ServiceContainer $app, AccessTokenInterface $accessToken = null)
    {
        $this->app = $app;
        $this->accessToken = $accessToken ? $accessToken : $this->app['access_token'];
    }

    /**
     * GET request.
     *
     * @param string $url
     * @param array  $query
     *
     * @return \Psr\Http\Message\ResponseInterface|\CarParts\Kernel\Support\Collection|array|object|string
     *
     * @throws \CarParts\Kernel\Exceptions\InvalidConfigException
     */
    public function httpGet($url, array $query = [])
    {
        return $this->request($url, 'GET', ['query' => $query]);
    }

    /**
     * POST request.
     *
     * @param string $url
     * @param array  $data
     *
     * @return \Psr\Http\Message\ResponseInterface|\CarParts\Kernel\Support\Collection|array|object|string
     *
     * @throws \CarParts\Kernel\Exceptions\InvalidConfigException
     */
    public function httpPost($url, array $data = [])
    {
        return $this->request($url, 'POST', ['form_params' => $data]);
    }

    /**
     * JSON request.
     *
     * @param string       $url
     * @param string|array $data
     * @param array        $query
     *
     * @return \Psr\Http\Message\ResponseInterface|\CarParts\Kernel\Support\Collection|array|object|string
     *
     * @throws \CarParts\Kernel\Exceptions\InvalidConfigException
     */
    public function httpPostJson($url, array $data = [], array $query = [])
    {
        return $this->request($url, 'POST', ['query' => $query, 'json' => $data]);
    }

    /**
     * Upload file.
     *
     * @param string $url
     * @param array  $files
     * @param array  $form
     * @param array  $query
     *
     * @return \Psr\Http\Message\ResponseInterface|\CarParts\Kernel\Support\Collection|array|object|string
     *
     * @throws \CarParts\Kernel\Exceptions\InvalidConfigException
     */
    public function httpUpload($url, array $files = [], array $form = [], array $query = [])
    {
        $multipart = [];

        foreach ($files as $name => $path) {
            $multipart[] = [
                'name'     => $name,
                'contents' => fopen($path, 'r'),
            ];
        }

        foreach ($form as $name => $contents) {
            $multipart[] = compact('name', 'contents');
        }

        return $this->request($url, 'POST', ['query' => $query, 'multipart' => $multipart]);
    }

    /**
     * @return AccessTokenInterface
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param \CarParts\Kernel\Contracts\AccessTokenInterface $accessToken
     *
     * @return $this
     */
    public function setAccessToken(AccessTokenInterface $accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @param string $url
     * @param string $method
     * @param array  $options
     * @param bool   $returnRaw
     *
     * @return \Psr\Http\Message\ResponseInterface|\CarParts\Kernel\Support\Collection|array|object|string
     *
     * @throws AuthorizationException
     * @throws Exceptions\InvalidConfigException
     * @throws ResourceNotFoundException
     * @throws ServiceInvalidException
     * @throws ValidationException
     */
    public function request($url, $method = 'GET', array $options = [], $returnRaw = false)
    {
        if (empty($this->middlewares)) {
            $this->registerHttpMiddlewares();
        }

        try {
            $response = $this->performRequest($url, $method, $options);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $content = $response->getBody()->getContents();
            $content = json_decode($content);
            $message = property_exists($content, 'message') ? $content->message : '';

            switch ($statusCode) {
                case 404:
                    throw new ResourceNotFoundException($message, 404);
                case 400:
                case 422:
                    throw new ValidationException($message, 400);
                    break;
                case 401:
                    throw new AuthorizationException($message, 401);
                default:
                    throw new ServiceInvalidException($message ? $message : 'Service Invalid', 500);
            }
        } catch (ServerException $e) {
            $response = $e->getResponse();
            $content = $response->getBody()->getContents();
            $content = json_decode($content);
            $message = property_exists($content, 'message') ? $content->message : 'Service Invalid';

            throw new ServiceInvalidException($message, 500);
        }

        return $returnRaw ? $response : $this->castResponseToType($response, $this->app->config->get('response_type'));
    }

    /**
     * @param string $url
     * @param string $method
     * @param array  $options
     *
     * @return \CarParts\Kernel\Http\Response
     *
     * @throws \CarParts\Kernel\Exceptions\InvalidConfigException
     */
    public function requestRaw($url, $method = 'GET', array $options = [])
    {
        return Response::buildFromPsrResponse($this->request($url, $method, $options, true));
    }

    /**
     * Return GuzzleHttp\Client instance.
     *
     * @return \GuzzleHttp\Client
     */
    public function getHttpClient()
    {
        if (!($this->httpClient instanceof Client)) {
            $this->httpClient = $this->app['http_client'] ? $this->app['http_client'] : new Client();
        }

        return $this->httpClient;
    }

    /**
     * Register Guzzle middlewares.
     */
    protected function registerHttpMiddlewares()
    {
        // retry
        $this->pushMiddleware($this->retryMiddleware(), 'retry');
        // access token
        $this->pushMiddleware($this->accessTokenMiddleware(), 'access_token');
    }

    /**
     * Attache access token to request query.
     *
     * @return \Closure
     */
    protected function accessTokenMiddleware()
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                if ($this->accessToken) {
                    $request = $this->accessToken->applyToRequest($request, $options);
                }

                return $handler($request, $options);
            };
        };
    }

    /**
     * Return retry middleware.
     *
     * @return \Closure
     */
    protected function retryMiddleware()
    {
        return Middleware::retry(function (
            $retries,
            RequestInterface $request,
            ResponseInterface $response = null
        ) {
            // Limit the number of retries to 2
            if ($retries <= $this->app->config->get('http.retries', 1) && $response) {
                if ($response->getStatusCode() == 401) {
                    $this->accessToken->refresh();

                    return true;
                }
            }

            return false;
        }, function () {
            return abs($this->app->config->get('http.retry_delay', 500));
        });
    }
}
