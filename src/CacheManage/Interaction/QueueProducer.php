<?php

namespace Yuzhua\EfficientCacheTools\CacheManage\Interaction;

use http\Exception\BadConversionException;

class QueueProducer
{
    /**
     * @var
     */
    private $channel;

    /**
     * @var
     */
    private $exchange;

    /**
     * @var string
     */
    private $routeKey;

    /**
     * @var array
     */
    private $config;

    /**
     * @var null
     */
    private $connection = null;

    /**
     * @var int
     * MQ连接超时时间
     */
    private $timeout = 30;

    /**
     * @var int
     * 心跳检测(超过当前时间未操作tcp连接断开)
     */
    private $heartbeat = 180;

    public function __construct($config){
        $queueConfig = config('rabbitmq');
        $this->config = [
            'host'  =>  $config['host'] ?? $queueConfig['host'],
            'port'  =>  $config['port'] ?? $queueConfig['port'],
            'login' =>  $config['user'] ?? $queueConfig['user'],
            'password'  =>  $config['password'] ?? $queueConfig['password'],
            'heartbeat' => $config['heartbeat'] ?? $this->heartbeat,
            'queue'   => $config['queue'],
            'ex_name' => $config['ex_name'],
            'vhost'   => $config['vhost'] ?? 'operation_center',
            'routing_key' => $config['routing_key'] ?? ''
        ];
        return $this->initialize();
    }

    /**
     * @throws \AMQPConnectionException
     * @throws \Exception
     */
    public function initialize() {
        $this->connection = new \AMQPConnection($this->config);
        if ($this->connection->connect() == false) {
            throw new \Exception("连接rabbit失败");
        }
        $this->connection->setWriteTimeout($this->timeout);
        $this->connection->setReadTimeout($this->timeout);
        return $this;
    }

    /**
     * @throws \AMQPConnectionException
     */
    public function setChannel() {
        if ($this->connection === null || $this->connection->isConnected() == false) {
            $this->initialize();
        }
        $this->channel || $this->channel = new \AMQPChannel($this->connection);
        return $this;
    }

    /**
     * @throws \AMQPConnectionException
     * @throws \AMQPExchangeException
     */
    public function setExchange() {
        //通道不可用重连
        if($this->channel && $this->channel->isConnected() == false)
        {
            $this->channel = null;
        }

        $this->channel || $this->setChannel();
        $this->exchange = new \AMQPExchange($this->channel);
        $this->exchange->setFlags(AMQP_DURABLE);
        $this->exchange->setName($this->config["ex_name"]);
        return $this;
    }

    /**
     * @param string $routeKey
     */
    public function setRouteKey(string $routeKey = "") {
        $this->routeKey = $this->config['routing_key'] ?? $routeKey;
        return $this;
    }

    /**
     * @return $this
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPQueueException
     */
    public function setQueue()
    {
        $que = new \AMQPQueue($this->channel);
        $que->setName($this->config['queue']);
        $que->setFlags(AMQP_DURABLE);
        $que->bind($this->config['ex_name'],$this->routeKey);

        return $this;
    }

    /**
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPExchangeException
     */
    public function push($message)
    {
        $messages = is_array($message) ? $message : [$message];
        try{
            $this->setChannel();
            $this->setRouteKey();
            $this->setQueue();
            $this->setExchange();

            foreach ($messages as $msg)
            {
                $this->exchange->publish($msg, $this->routeKey , AMQP_NOPARAM , ['content_type' => 'application/json']);
            }
        }catch (AMQPException $exception) {
            throw new \Exception('缓存消息推送失败');
        }
    }

    public function __destruct()
    {
        $this->connection == null || $this->connection->disconnect();
    }
}
