<?php


namespace Yuzhua\EfficientCacheTools\Method;

use Yuzhua\EfficientCacheTools\Loader\CacheManageEnum;
use function Yuzhua\EfficientCacheTools\Loader\getDomain;
use function Yuzhua\EfficientCacheTools\Loader\getPageRoute;

class MainCacheManage
{
    /**
     * 自定义配置文件
     * @var array|mixed
     */
    public $config = [];

    /**
     * redis缓存操作服务层
     * @var null
     */
    public $redisCacheService = null;

    /**
     * 文件缓存操作服务层
     * @var null
     */
    public $fileCacheService = null;

    public function __construct()
    {
        $this->config = [];

    }

    public static function getCacheDirver($driverType){
        switch ($driverType){
            case CacheManageEnum::DRIVER_TYPE_1:
                return new Redis();
            case CacheManageEnum::DRIVER_TYPE_2:
                return new File();
            case CacheManageEnum::DRIVER_TYPE_3:
                return new Memcached();
            default :
                return new Redis();
        }
    }
    
    public static function makeParamsForQueue(array $params):string
    {
        $params['valid_time'] = isset($params['valid_time']) ? $params['valid_time'] : 0;
        $params['domain'] =  getDomain();
        $params['page_route'] = getPageRoute();
        $params['expire_time'] = time() + intval($params['valid_time']);
        return json_encode($params,JSON_UNESCAPED_UNICODE);
    }
}
