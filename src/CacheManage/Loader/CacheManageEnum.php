<?php


namespace Yuzhua\EfficientCacheTools\CacheManage\Loader;


class CacheManageEnum
{
    /**
     * 数据类型一级
     * @var integer
     */
    const DATA_TYPE_ONE_1 = 1;
    const DATA_TYPE_ONE_2 = 2;
    const DATA_TYPE_ONE_3 = 3;
    const DATA_TYPE_ONE_4 = 4;
    const DATA_TYPE_ONE_ARR = [
        self::DATA_TYPE_ONE_1 => '图片',
        self::DATA_TYPE_ONE_2 => '文章',
        self::DATA_TYPE_ONE_3 => '分公司地址',
        self::DATA_TYPE_ONE_4 => '快商通',
    ];

    /**
     * 数据类型二级
     * @var integer
     */
    const DATA_TYPE_TWO_1 = 1;
    const DATA_TYPE_TWO_2 = 2;
    const DATA_TYPE_TWO_3 = 3;
    const DATA_TYPE_TWO_4 = 4;
    const DATA_TYPE_TWO_5 = 5;
    const DATA_TYPE_TWO_7 = 7;
    const DATA_TYPE_TWO_8 = 8;
    const DATA_TYPE_TWO_9 = 9;
    const DATA_TYPE_TWO_ARR = [
        self::DATA_TYPE_TWO_1 => 'LOGO',
        self::DATA_TYPE_TWO_2 => 'BANNER(头部)',
        self::DATA_TYPE_TWO_3 => 'BANNER(中部)',
        self::DATA_TYPE_TWO_4 => 'BANNER(尾部)',
        self::DATA_TYPE_TWO_5 => '背景图',
        self::DATA_TYPE_TWO_7 => '热区图',
        self::DATA_TYPE_TWO_8 => '通栏',
        self::DATA_TYPE_TWO_9 => '文章',
    ];

    /**
     * 是否对接了运营中台
     * @var integer
     */
    const OP_CONN_STATUS_1 = 1;
    const OP_CONN_STATUS_2 = 2;
    const OP_CONN_STATUS_TYPE = [
        self::OP_CONN_STATUS_1 => '是',
        self::OP_CONN_STATUS_2 => '否',
    ];

    /**
     * 缓存驱动类型
     * @var integer
     */
    const DRIVER_TYPE_1 = 1;
    const DRIVER_TYPE_2 = 2;
    const DRIVER_TYPE_3 = 3;
    const DRIVER_TYPE_ = [
        self::DRIVER_TYPE_1 => 'redis',
        self::DRIVER_TYPE_2 => 'file',
        self::DRIVER_TYPE_3 => 'memcached',
    ];

    /**
     * 缓存类型
     * @var integer
     */
    const CACHE_TYPE_1 = 1;
    const CACHE_TYPE_2 = 2;
    const CACHE_TYPE_3 = 3;
    const CACHE_TYPE_ = [
        self::CACHE_TYPE_1 => '内存缓存',
        self::CACHE_TYPE_2 => '磁盘缓存',
        self::CACHE_TYPE_3 => '数据库缓存',
    ];
}
