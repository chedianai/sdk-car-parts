<?php

namespace CarParts\Vehicle;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class ServiceProvider.
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}.
     */
    public function register(Container $app)
    {
        $app['vehicle'] = function ($app) {
            return new Client($app);
        };
    }
}
