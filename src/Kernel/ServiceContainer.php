<?php

namespace CarParts\Kernel;

use CarParts\Kernel\Providers\AccessTokenServiceProvider;
use CarParts\Kernel\Providers\ConfigServiceProvider;
use CarParts\Kernel\Providers\HttpClientServiceProvider;
use CarParts\Kernel\Providers\RequestServiceProvider;
use Pimple\Container;

/**
 * Class ServiceContainer.
 *
 * @property \CarParts\Kernel\Config                  $config
 * @property \Symfony\Component\HttpFoundation\Request  $request
 * @property \GuzzleHttp\Client                         $http_client
 */
class ServiceContainer extends Container
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var array
     */
    protected $providers = [];

    /**
     * @var array
     */
    protected $defaultConfig = [];

    /**
     * @var array
     */
    protected $userConfig = [];

    /**
     * Constructor.
     *
     * @param array       $config
     * @param array       $prepends
     * @param string|null $id
     */
    public function __construct(array $config = [], array $prepends = [], $id = null)
    {
        $this->registerProviders($this->getProviders());

        parent::__construct($prepends);

        $this->userConfig = $config;

        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id ? $this->id : ($this->id = md5(json_encode($this->userConfig)));
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $base = [
            // http://docs.guzzlephp.org/en/stable/request-options.html
            'http' => [
                'timeout' => 5.0,
                'base_uri' => 'http://carparts.chedianai.com/',
                'headers' => ['Accept' => 'application/json']
            ],
        ];

        return array_replace_recursive($base, $this->defaultConfig, $this->userConfig);
    }

    /**
     * Return all providers.
     *
     * @return array
     */
    public function getProviders()
    {
        return array_merge([
            ConfigServiceProvider::class,
            RequestServiceProvider::class,
            HttpClientServiceProvider::class,
            AccessTokenServiceProvider::class
        ], $this->providers);
    }

    /**
     * Magic get access.
     *
     * @param string $id
     *
     * @return mixed
     */
    public function __get($id)
    {
        return $this->offsetGet($id);
    }

    /**
     * Magic set access.
     *
     * @param string $id
     * @param mixed  $value
     */
    public function __set($id, $value)
    {
        $this->offsetSet($id, $value);
    }

    /**
     * @param array $providers
     */
    public function registerProviders(array $providers)
    {
        foreach ($providers as $provider) {
            parent::register(new $provider());
        }
    }
}
