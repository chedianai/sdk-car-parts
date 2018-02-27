<?php

namespace CarParts;

use CarParts\Kernel\ServiceContainer;

/**
 * Class Application.
 *
 * @property \CarParts\Category\Client $category
 * @property \CarParts\Item\Client     $item
 * @property \CarParts\Vehicle\Client  $vehicle
 */
class Application extends ServiceContainer
{
    /**
     * @var array
     */
    protected $providers = [
        Category\ServiceProvider::class,
        Item\ServiceProvider::class,
        Vehicle\ServiceProvider::class,
    ];
}
