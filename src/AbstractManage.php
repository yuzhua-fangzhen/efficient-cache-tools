<?php


namespace Yuzhua\EfficientCacheTools;


abstract class AbstractManage
{
    abstract protected function clear($data,$cacheConfig);
}
