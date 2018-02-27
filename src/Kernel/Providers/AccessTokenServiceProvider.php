<?php

namespace CarParts\Kernel\Providers;

use CarParts\Kernel\AccessToken;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class AccessTokenServiceProvider.
 */
class AccessTokenServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}.
     */
    public function register(Container $app)
    {
        !isset($app['access_token']) && $app['access_token'] = function ($app) {
            return new AccessToken($app);
        };
    }
}
