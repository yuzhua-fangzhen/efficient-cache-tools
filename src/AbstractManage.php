<?php


namespace Yuzhua\EfficientCacheTools;


abstract class AbstractManage
{
    abstract protected function store();

    abstract protected function delete();
}
