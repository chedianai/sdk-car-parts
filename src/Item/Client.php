<?php
namespace CarParts\Item;

use CarParts\Kernel\BaseClient;
use CarParts\Kernel\Support\Collection;

/**
 * Class Client.
 */
class Client extends BaseClient
{

    /**
     * 所有商品列表
     * @param array $params 包括：category, vehicle_id, brand, keyword
     * @return Collection
     */
    public function items($params = [])
    {
        return $this->httpGet('api/v1/items', $params);
    }

    /**
     * 根据 ID 列表获取配件列表
     * @param array $ids ID 列表，数组形式
     * @return Collection
     */
    public function itemsByIds($ids)
    {
        $idsStr = implode(',', $ids);
        return $this->httpGet('api/v1/items/by_ids', ['ids' => $idsStr]);
    }

    /**
     * 车型推荐商品（分类编排）
     * @param string $vehicleId
     * @param array $params
     * @param bool $withExcluded
     * @return Collection
     */
    public function itemsGroupByCategory($vehicleId, $categories = [], $withExcluded = false)
    {
        $params = [
            'vehicle_id' => $vehicleId
        ];

        $categoriesStr = implode(',', $categories);

        if ($categoriesStr) {
            $params['categories'] = $categoriesStr;
        }

        if ($withExcluded) {
            $params['with_excluded'] = $withExcluded;
        }

        return $this->httpGet('api/v1/items/group_by_category', $params);
    }

    /**
     * 商品详情
     * @param string $id 商品 ID
     * @return Collection
     */
    public function detail($id)
    {
        return $this->httpGet('api/v1/items/' . $id);
    }

    /**
     * 获取车型匹配同产品所有规格产品
     * @return Collection
     */
    public function pairItems($vehicleId, $itemId)
    {
        return $this->httpGet('api/v1/items/pair_items', ['vehicle_id' => $vehicleId, 'item_id' => $itemId]);
    }
}
