<?php


namespace CacheManage;


abstract class AbstractManage
{
    abstract protected function store();

    abstract protected function delete();
}
