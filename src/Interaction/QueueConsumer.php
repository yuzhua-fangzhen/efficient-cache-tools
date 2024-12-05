<?php


namespace Yuzhua\EfficientCacheTools\Interaction;


use Yuzhua\EfficientCacheTools\Method\MainCacheManage;

class QueueConsumer
{
    /**
     * @var null
     */
    private $queueConfig = null;

    /**
     * @var null
     */
    private $project = null;

    /**
     * @var null
     */
    private $type = null;

    /**
     * @var null
     */
    private $platformClass = null;

    /**
     * @var null
     */
    private $cacheConfig = null;

    public function __construct($queueConfig,$cacheConfig,$project,$type,$platformClass){
        set_time_limit(0);
        $queueConfig['queueName'] = sprintf('operation_center.client_cache_manage_clear_project_%s_type_%s_platform_class_%s.sync.que',$project,$type,$platformClass);
        $this->queueConfig = $queueConfig;
        $this->cacheConfig = $cacheConfig;
        $this->project = $project;
        $this->type = $type;
        $this->platformClass = $platformClass;
    }

    public function consume(){

        $this->syncConsumeByQueue();
    }


    public function syncConsumeByQueue()
    {
        while(true){
            $amqp = null;
            try{
                $amqp = new AMQPHelper($this->queueConfig);
                $amqp->ttl = 1;
                $amqp->connect();
                $amqp->consume([$this, 'consumeCallback'], function($message){
                    //dd($message);
                });
                $amqp->disconnect();
            }catch(\Exception $e){
                if($amqp){
                    $amqp->disconnect();
                }
                if($e->getMessage() != 'Consumer timeout exceed'){
                    throw new \Exception($e->getMessage());
                }
            }
            sleep(1);
        }
    }

    public function consumeCallback($data)
    {
        if(!is_array($data)){
            $data = json_decode($data,true);
        }

        if($data['project'] == $this->project && $data['platform_class'] == $this->platformClass && $this->type == $data['type']){
            MainCacheManage::getCacheDirver($data['driver_type'])->clear($data,$this->cacheConfig);
        }

        return true;
    }
}
