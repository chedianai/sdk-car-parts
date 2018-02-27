<?php

namespace CarParts;

use CarParts\Kernel\ServiceContainer;

/**
 * Class Application.
 *
 * @property \CarParts\Brand\Client    $brand
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
        Brand\ServiceProvider::class,
        Category\ServiceProvider::class,
        Item\ServiceProvider::class,
        Vehicle\ServiceProvider::class,
    ];
}
