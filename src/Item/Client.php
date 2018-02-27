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
     * 车型推荐商品（分类编排）
     * @param string $vehicleId
     * @param array $params
     * @return Collection
     */
    public function itemsGroupByCategory($vehicleId)
    {
        return $this->httpGet('api/v1/items/group_by_category', ['vehicle_id' => $vehicleId]);
    }

    /**
     * 车型推荐商品（分类编排）
     * @param string $vehicleId
     * @param array $params
     * @return Collection
     */
    public function recommendItemsGroupByCategory($vehicleId, $params = [])
    {
        $params = array_merge($params, ['vehicle_id' => $vehicleId]);

        return $this->httpGet('api/v1/items/group_by_category/recommend', $params);
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
