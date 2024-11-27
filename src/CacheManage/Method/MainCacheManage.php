<?php


namespace Yuzhua\EfficientCacheTools\CacheManage\Method;

use function Yuzhua\EfficientCacheTools\CacheManage\Loader\getPageRoute;

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

    public function __construct(
        Redis $redisCacheService,
        File $fileCacheService
    )
    {
        $this->config = [];

    }

    public function getList($page = 1,$perPage = 10)
    {

    }

    public function delete(){

    }

    public static function getPageRoute()
    {
        return getPageRoute();
    }
}
