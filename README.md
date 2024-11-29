<h1 align="center">EfficientCacheTools</h1>

<p align="center">

[![Minimum PHP Version](http://img.shields.io/badge/php-%3E%3D%205.6-8892BF.svg)](https://php.net/)

</p>


> 本扩展包仅供团队内部使用，以下流程为公司前台项目对接文档，如果发现此包有 bug，欢迎随时提 PR，希望各位同学使用愉快。
> 
> 仓库地址：https://github.com/yuzhua-fangzhen/efficient-cache-tools
> 
> 包地址：https://packagist.org/packages/yuzhua/efficient-cache-tools


## 安装

```shell
composer require yuzhua/efficient-cache-tools dev-main
```

## 运行环境

- PHP >= 5.6
- Composer

## 前台生成缓存时推送运营中台消费队列参数说明
  
参数 | 描述 | 备注 | 是否必填 | 示例 
:---: | :---: | :---: | :---: | :---:
project | 品牌ID | | 是 | 3
platform_class | 平台分类 | 见CacheManageEnum类枚举 | 是 | 1
page_title | 首页标题 | 自定义| 是 | 商标列表页 
data_type_one | 数据类型一级 | 见CacheManageEnum类枚举 | 是 | 1
data_type_two | 数据类型二级 | 见CacheManageEnum类枚举 | 是 | 1
center_slug | 运营中台标识 | 非必填 | 否 | home-braner
op_conn_status | 是否对接运营中台 | 见CacheManageEnum类枚举 | 是 | 1
driver_type | 缓存驱动类型 | 见CacheManageEnum类枚举 | 是 | 1
cache_type  | 缓存类型 | 见CacheManageEnum类枚举 | 是 | 1
cache_name  | 缓存名称 | 必须与项目操作的换名对应 | 是 | mh:getBanner1
cache_data  | 缓存内容 | 内容为运营中台接口的返回值 | 是 | 
valid_time  | 缓存时间 | 单位(秒) | 是 | 600
extra_info  | 扩展信息 | 自定义,无特殊情况传入请求缓存中台的接口入参 | 否 |

```angular2html
  以下示例均以laravel框架进行,后续各平台对接自行调整
```

### 使用说明
```php
<?php
    
    #第一步:公共方法实例化队列生产者,入参当前项目的RABBITMQ配置,方便后续调用
    use Yuzhua\EfficientCacheTools\Interaction\QueueProducer;
    
    function operationQueue(){
        return new QueueProducer([
            'host' => env('RABBITMQ_HOST', 'xx'),
            'port' => env('RABBITMQ_PORT', 'xxx'),
            'user' => env('RABBITMQ_USER', 'xxx'),
            'password' => env('RABBITMQ_PASSWORD', 'xxx'),
        ]);
    }
    
    #第二步:调用MainCacheManage类的makeParamsForQueue方法(强制要求)，传入定义好的参数,并推送消息
    use Yuzhua\EfficientCacheTools\Loader\CacheManageEnum;
    use Yuzhua\EfficientCacheTools\Method\MainCacheManage;
 
    public function getBanner($params,$platform_class = 1)
    {
        $cache_name = 'mh:getBanner'.$platform_class;
        $banner = unserialize(Jaeager::make()->redisGet($cache_name));
        $bool = config('app.env') == 'production' ? false : true;
        if(!$banner || $bool){
            $banner = $this->url('pictures_list')->params($params)->send();
            if (isset($banner['pagination']['total']) && $banner['pictures'] &&
                $banner['pagination']['total'] > 0 && !empty($banner['pictures'])
            ){
                $banner = collect($banner['pictures'])->filter(function ($value){
                    return $value['end_at'] >= time() && $value['start_at'] <= time();
                })->toArray();
                $banner = array_slice($banner,0,10);
                Jaeager::make()->redisSetex($cache_name,600,serialize($banner),false);
                
                //对接运营中台开始
                $cacheMessage = MainCacheManage::makeParamsForQueue([
                    'project' => 3,
                    'platform_class' => $platform_class,
                    'page_title' => '首页',
                    'data_type_one' => CacheManageEnum::DATA_TYPE_ONE_1,
                    'data_type_two' => CacheManageEnum::DATA_TYPE_TWO_2,
                    'center_slug' => $params['slug'] ?? '',
                    'op_conn_status' => CacheManageEnum::OP_CONN_STATUS_1,
                    'driver_type' => CacheManageEnum::DRIVER_TYPE_1,
                    'cache_type' => CacheManageEnum::CACHE_TYPE_1,
                    'cache_name' => $cache_name,
                    'cache_data' => $banner,
                    'valid_time' => 600,
                    'extra_info' => ['搜索条件' => $params],
                ]);
                operationQueue()->push($cacheMessage);
                //对接运营中台结束
            }else{
                $banner = [];
            }
        }
        return $banner;
    }
```

## 前台消费运营中台的广播消息
```angular2html
  当前包里只处理了清除缓存逻辑,后续继续拓展
```

### 使用说明
```angular2html
  新增守护进程,代码示例如下
```

```php
<?php
    namespace App\Console\Commands\Queue;


    use Illuminate\Console\Command;
    use Yuzhua\EfficientCacheTools\Interaction\QueueConsumer;
    use Yuzhua\EfficientCacheTools\Loader\CacheManageEnum;
    
    
    class CacheManageClear extends Command
    {
        /**
         * @var string
         */
        protected $signature = 'queue:cache-manage-clear';
    
        /**
         * The console command description.
         *
         * @var string
         */
        protected $description = '缓存管理-清除缓存';
    
        public function handle()
        {
            $queueConfig = [
                'host' => env('RABBITMQ_HOST', 'xx'),
                'port' => env('RABBITMQ_PORT', 'xxx'),
                'user' => env('RABBITMQ_USER', 'xxx'),
                'password' => env('RABBITMQ_PASSWORD', 'xxx'),
            ];
    
            $cacheConfig = [
                'redis' => [
                    'host' => env('REDIS_HOST', 'xxx'),
                    'password' => env('REDIS_PASSWORD', 'xxx'),
                    'port' => env('REDIS_PORT', 6379),
                    'database' => env('REDIS_DATABASE', 1),
                ]
            ];
            //实例化QueueConsumer类并入参
            //队列配置
            //项目用到的各缓存配置
            //品牌ID
            //平台分类    
            (new QueueConsumer($queueConfig,$cacheConfig,3,CacheManageEnum::PLATFORM_CLASS_1))->consume();
        }
    } 
```
  
