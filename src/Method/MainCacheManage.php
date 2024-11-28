<?php


namespace Yuzhua\EfficientCacheTools\Method;

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

    public static function makeParamsForQueue(array $params):string
    {
        //$params['domain'] =  getDomain();
        $params['domain'] =  $_SERVER['HTTP_HOST'];
        $params['page_route'] = getPageRoute();
        $params['expire_time'] = time() + (intval($params['valid_time']) ?? 0);
        return json_encode($params,JSON_UNESCAPED_UNICODE);
    }
}
