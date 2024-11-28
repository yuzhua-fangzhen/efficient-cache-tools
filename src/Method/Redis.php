<?php

namespace Yuzhua\EfficientCacheTools\Method;



use Yuzhua\EfficientCacheTools\AbstractManage;

class Redis extends AbstractManage
{
    public function clear($data,$cacheConfig)
    {
        if(isset($cacheConfig['redis']) && !empty($cacheConfig['redis'])){
            $redisConfig = $cacheConfig['redis'];

            $redis = new \Redis();
            $redis->connect($redisConfig['host'], $redisConfig['port']);
            isset($redisConfig['password']) && $redis->auth($redisConfig['password']);
            isset($redisConfig['database']) && $redis->select($redisConfig['database']);
            
            $redis->del($data['cache_name']);
        }
    }
}
