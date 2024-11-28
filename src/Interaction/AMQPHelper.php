<?php
namespace Yuzhua\EfficientCacheTools\Interaction;


/**
 * Class AMQPHelper
 * @package Yuzhua\EfficientCacheTools\Interaction
 */
class AMQPHelper
{

    /**
     * 连接信息
     * @var string
     */
    public $host = '';
    public $port = '';
    public $vhost = '';
    public $login = '';
    public $password = '';
    public $readTimeout = 60000;
    public $writeTimeout = 60000;
    public $connectTimeout = 60000;
    public $heartbeat = 3;

    /**
     * 连接
     * @var \AMQPConnection
     */
    public $connection;

    /**
     * 通道
     * @var \AMQPChannel
     */
    public $channel;

    /**
     * 队列
     * @var \AMQPQueue
     */
    public $queue;

    /**
     * 交换器
     * @var \AMQPExchange
     */
    public $exchange;

    /**
     * TTL 队列
     * @var \AMQPQueue
     */
    public $ttlQueue;

    /**
     * TTL 交换器
     * @var \AMQPExchange
     */
    public $ttlExchange;

    /**
     * 队列名称
     * @var string
     */
    public $queueName = '';

    /**
     * 交换器名称
     * @var string
     */
    public $exchangeName = '';

    /**
     * TTL 延迟时间，单位：秒
     * @var int
     */
    public $ttl = 0;

    /**
     * TTL 交换器名称
     * @var string
     */
    public $ttlExchangeName = '';

    /**
     * TTL 队列名称
     * @var string
     */
    public $ttlQueueName = '';

    /**
     * 一条消息消费失败后 sleep 的时间，单位：1/1000000 秒
     * @var int
     */
    public $usleep = 500000;

    /**
     * AMQPHelper constructor.
     * @param array $queueConfig
     */
    public function __construct($queueConfig = [])
    {
        $this->host = $queueConfig['host'];
        $this->port = $queueConfig['port'];
        $this->vhost = 'operation_center';
        $this->login = $queueConfig['user'];
        $this->password = $queueConfig['password'];
        $this->exchangeName = 'operation_center.client_cache_manage_clear.dir.ex';
        $this->queueName = $queueConfig['queueName'];
    }

    /**
     * 建立连接、建立通道、定义交换器、定义队列
     * @throws \Exception
     */
    public function connect()
    {
        $this->connection = new \AMQPConnection(array(
            'host' => $this->host,
            'port' => $this->port,
            'vhost' => $this->vhost,
            'login' => $this->login,
            'password' => $this->password,
            'read_timeout' => $this->readTimeout,
            'write_timeout' => $this->writeTimeout,
            'connect_timeout' => $this->connectTimeout,
            'heartbeat' => $this->heartbeat
        ));
        $this->connection->connect();
        $this->channel = new \AMQPChannel($this->connection);
        $this->channel->qos(0, 1);
        register_shutdown_function(function(){
            $this->closeChannel();
            $this->closeConnection();
        });
        if($this->exchangeName){
            $this->exchange = new \AMQPExchange($this->channel);
            $this->exchange->setName($this->exchangeName);
            $this->exchange->setType(AMQP_EX_TYPE_FANOUT);
            $this->exchange->setFlags(AMQP_DURABLE);
            $this->exchange->declareExchange();
        }
        if($this->queueName){
            $this->queue = new \AMQPQueue($this->channel);
            $this->queue->setName($this->queueName);
            $this->queue->setFlags(AMQP_DURABLE);
            $this->queue->declareQueue();
            if($this->exchangeName){
                $this->queue->bind($this->exchangeName);
            }
        }
    }

    /**
     * 消费者进程，监听队列
     * @param callable $callback
     * @param callable|null $logger
     * @throws \Exception
     */
    public function consume($callback, $logger = null)
    {
        $consumer_tag = 'tag_'.date('YmdHis').'_'.uniqid().'_'.str_pad(strval(rand(0, 999999)), 6, STR_PAD_LEFT, '0');
        $this->queue->consume(function(\AMQPEnvelope $envelope, \AMQPQueue $queue) use($callback, $logger){
            if(!is_callable($logger)){
                $logger = function($message){};
            }
            $tag = $envelope->getDeliveryTag();
            $content_type = $envelope->getContentType();
            $body = $envelope->getBody();

            $logger([
                'tag' => 'ConsumeGet',
                'get' => $tag,
                'content_type' => $content_type,
                'body' => $body
            ]);
            $json_type = 'application/json';

            if($content_type && substr($content_type, 0, strlen($json_type)) == $json_type){
                $body = json_decode($body, true);
            }
            try{
                $result = $callback($body);
            }catch(\Exception $e){
                $result = false;
                throw new \Exception('消息 回调 失败');
            }
            if($result){
                if($queue->ack($tag)){
                    $logger([
                        'tag' => 'ConsumeAck',
                        'ack' => $tag,
                        'body' => $body
                    ]);
                }else{
                    if($queue->reject($tag, AMQP_REQUEUE)){
                        $logger([
                            'tag' => 'ConsumeReject',
                            'reject' => $tag,
                            'body' => $body
                        ]);
                    }else{
                        $logger([
                            'tag' => 'ConsumeReject',
                            'reject' => $tag,
                            'body' => $body,
                            'message' => '消息 ACK 失败'
                        ]);
                        yii::warning([
                            'tag' => 'ConsumeReject',
                            'reject' => $tag,
                            'body' => $body,
                            'message' => '消息 ACK 失败'
                        ]);
                        throw new \Exception('消息 ACK 失败');
                    }
                }
            } else{
                usleep($this->usleep);
                if($queue->reject($tag, AMQP_REQUEUE)){
                    $logger([
                        'tag' => 'ConsumeReject',
                        'reject' => $tag,
                        'body' => $body
                    ]);
                }else{
                    $logger([
                        'tag' => 'ConsumeReject',
                        'reject' => $tag,
                        'body' => $body,
                        'message' => '消息 REJECT 失败'
                    ]);
                    throw new \Exception('消息 REJECT 失败');
                }
            }
        }, AMQP_NOPARAM, $consumer_tag);
    }

    /**
     * 关闭通道和连接
     */
    public function disconnect()
    {
        $this->closeChannel();
        $this->closeConnection();
        $this->queue = null;
        $this->exchange = null;
        $this->ttlQueue = null;
        $this->ttlExchange = null;
        $this->channel = null;
        $this->connection = null;
    }

    /**
     * 关闭通道
     */
    public function closeChannel()
    {
        try{
            if($this->channel){
                $this->channel->close();
            }
        }catch(\Exception $e){}
    }

    /**
     * 关闭连接
     */
    public function closeConnection()
    {
        try{
            if($this->connection){
                $this->connection->disconnect();
            }
        }catch(\Exception $e){}
    }

}
