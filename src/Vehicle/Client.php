<?php
namespace CarParts\Vehicle;

use CarParts\Kernel\BaseClient;
use CarParts\Kernel\Support\Collection;

/**
 * Class Client.
 */
class Client extends BaseClient
{

    /**
     * 根据 VIN 码获取车型信息
     * @param $vin
     * @return Collection
     */
    public function findByVIN($vin)
    {
        return $this->httpGet('api/v1/vehicles/by_vin', ['vin' => $vin]);
    }

    /**
     * 车辆销售版本列表
     * @param $modelId
     * @return Collection
     */
    public function vehicles($modelId)
    {
        return $this->httpGet('api/v1/vehicles', ['vehicle_model_id' => $modelId]);
    }

    /**
     * 车型列表
     * @param $brandId
     * @return Collection
     */
    public function models($brandId)
    {
        return $this->httpGet('api/v1/vehicles/models', ['vehicle_brand_id' => $brandId]);
    }

    /**
     * 车辆品牌列表
     * @return Collection
     */
    public function brands()
    {
        return $this->httpGet('api/v1/vehicles/brands');
    }
}
