<?php
namespace CarParts\Category;

use CarParts\Kernel\BaseClient;
use CarParts\Kernel\Support\Collection;

/**
 * Class Client.
 */
class Client extends BaseClient
{

    /**
     * 配件分类
     * @return Collection
     */
    public function categories()
    {
        return $this->httpGet('api/v1/categories');
    }
}
