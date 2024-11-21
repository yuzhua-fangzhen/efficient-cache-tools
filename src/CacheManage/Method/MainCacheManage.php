<?php


namespace Yuzhua\EfficientCacheTools\CacheManage\Method;

class MainCacheManage
{
    /**
     * 品牌
     * @var integer
     */
    protected $project;

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

    public function getList($page = 1,$perPage = 10){

    }

    public function delete(){

    }
}
