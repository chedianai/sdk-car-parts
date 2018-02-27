# 车型库 SDK

>author JohnWang <wangjiajun@chedianai.com>

## 使用说明 ## 

此 SDK 仅限`车店AI`内部项目使用,请勿外泄;

### 配置 ### 

### Composer 引入 ###

```json
{
    "require": {
		"chedianai/car-parts-sdk":"~1.0"
    }
}
```

### 使用实例 ###

```

public function debug()
{
        $app = new \CarParts\Application([
            /**
             * 账号基本信息
             */
            'client_id'     => 'yPA16XMemwaWpy227WKyORx7QLrKEZgq',
            'client_secret' => 'xxHqlpVsdHUCwxADBMAdy8L3Yrm8p5oCRGbil8Id',

            /**
             * 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
             */
            'response_type' => 'collection',

            /**
             * 接口请求相关配置，超时时间等，具体可用参数请参考：
             * http://docs.guzzlephp.org/en/stable/request-config.html
             *
             * - retries: 重试次数，默认 1，指定当 http 请求失败时重试的次数。
             * - retry_delay: 重试延迟间隔（单位：ms），默认 500
             * - log_template: 指定 HTTP 日志模板，请参考：https://github.com/guzzle/guzzle/blob/master/src/MessageFormatter.php
             */
            'http'          => [
                'retries'     => 1,
                'retry_delay' => 500,
                'timeout'     => 5.0,
                // 'base_uri' => 'http://carparts.chedianai.com/', // 如果你在使用开发环境，则可以覆盖该参数
            ],
        ]);

        /**
         * 注入缓存实例，用于 AccessToken 缓存，必须为 Psr\SimpleCache\CacheInterface 实例
         */
        // $app['cache'] = new \CarParts\Kernel\Support\CacheBridge(app('cache.store'));

        try {
            dd(
                // 获取分类下所有品牌
                $app->brand->brands('ENGINE_OIL'),

                // 配件分类
                $app->category->categories(),

                // 车辆品牌列表
                $app->vehicle->brands(),

                // 车型列表
                $app->vehicle->models(9),

                // 车辆销售版本列表
                $app->vehicle->vehicles(292),

                // 根据 VIN 码获取车型信息
                $app->vehicle->findByVIN('LVRHDFACXFN377424'),

                // 所有商品列表
                $app->item->items(),

                // 车型推荐商品（分类编排）
                $app->item->itemsGroupByCategory('b41ba195'),

                // 车型推荐商品（分类编排）
                $app->item->recommendItemsGroupByCategory('b41ba195', ['categories' => 'ENGINE_OIL,OIL_FILTER']),

                // 商品详情
                $app->item->detail('e468c069'),

                //获取车型匹配同产品所有规格产品
                $app->item->pairItems('b41ba195', 'e468c069')
            );
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            echo $e->getMessage();
        }
}

```