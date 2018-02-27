<?php
namespace CarParts\Brand;

use CarParts\Kernel\BaseClient;
use CarParts\Kernel\Support\Collection;

/**
 * Class Client.
 */
class Client extends BaseClient
{

    /**
     * 获取分类下所有品牌
     * @return Collection
     */
    public function brands($category)
    {
        return $this->httpGet('api/v1/brands', ['category' => $category]);
    }
}
