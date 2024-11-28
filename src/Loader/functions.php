<?php

namespace Yuzhua\EfficientCacheTools\Loader;

if (!\function_exists('Yuzhua\EfficientCacheTools\Loader\getCacheName')) {
    function getCacheName()
    {

    }
}

if (!\function_exists('Yuzhua\EfficientCacheTools\Loader\getPageRoute')) {
    function getPageRoute()
    {
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $requestUri = $_SERVER['REQUEST_URI'];
        $relativePath = str_replace($scriptName, '', $requestUri);
        return $relativePath;
    }
}
if (!\function_exists('Yuzhua\EfficientCacheTools\Loader\getDomain')) {
    function getDomain()
    {
        return $_SERVER['HTTP_HOST'];
    }
}


