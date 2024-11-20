<?php


namespace CacheManage\Loader;


class Loader
{
    public static function loadConfig()
    {
        $configFile = __DIR__ . '/config/opcache.php';
        if (file_exists($configFile)) {
            $config = require $configFile;
        } else {
            // 配置文件不存在直接抛出异常
            throw new \HttpResponseException('缓存工具类缺少配置文件');
        }
        return $config ?? [];
    }
}
