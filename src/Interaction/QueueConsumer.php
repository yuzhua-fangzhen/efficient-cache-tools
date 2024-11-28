<?php


namespace Yuzhua\EfficientCacheTools\Interaction;


use console\helper\AMQPHelper;

class QueueConsumer
{
    /**
     * @var null
     */
    private $config = null;

    /**
     * @var null
     */
    private $project = null;

    /**
     * @var null
     */
    private $platformClass = null;

    /**
     * @var null
     */
    private $cacheConfig = null;

    public function __construct($config,$project,$platformClass,$cacheConfig){
        set_time_limit(0);
        $this->config = $config;
        $this->project = $project;
        $this->platformClass = $platformClass;
        $this->cacheConfig = $cacheConfig;
    }

    public function consume(){

        $this->syncConsumeByQueue($queueConfig['cache_manage_store_queue'], $exchangeConfig['cache_manage_exchange']);
    }

    /**
     * @param string $queue
     * @param string $exchange
     */
    public function syncConsumeByQueue($queue, $exchange)
    {
        while(true){
            $amqp = null;
            try{
                $amqp = new AMQPHelper();
                $amqp->ttl = 1;
                $amqp->exchangeName = $exchange;
                $amqp->queueName = $queue;
                $amqp->connect();
                $amqp->consume([$this, 'consumeCallback'], function($message){
                    yii::warning($message);
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

    public function consumeCallback($data,$redisConfig)
    {

    }
}
