<?php
namespace TenWebQueue;

use Enqueue\Fs\FsConnectionFactory;
use Interop\Queue\Context;
use Interop\Queue\Queue;
use TenWebQueue\DTO\QueueConfigDTO;
use TenWebQueue\DTO\QueueDataDTO;
use TenWebQueue\Exceptions\QueueException;
use TenWebQueue\Interfaces\QueueConsumerInterface;
use TenWebQueue\Interfaces\QueueProducerInterface;
use TenWebQueue\Traits\QueueRestart;


class QueueContext
{
    use QueueRestart;

    /**
     * @var Context $context
     */
    private $context = null;
    /**
     * @var Queue $queue
     */
    private $queue;
    /**
     * @var QueueProducerInterface $producer
     */
    private $producer;

    /**
     * @var QueueConsumerInterface $consumer
     */
    private $consumer;

    /**
     * @var QueueConfigDTO $config
     */
    private $config;

    /**
     * @var string[]
     */
    private $connectionTypes = ['file'];


    /**
     * @param QueueConfigDTO $config
     *
     * @throws QueueException
     */
    private function __construct($config)
    {
        $this->config = $config;
        $this->setStartTime(microtime(true));
        $this->purgeQueue();
        $this->createContext();
    }

    /**
     * @param $producerClass
     * @param $consumerClass
     *
     * @return void
     */
    public function setUpQueue($producerClass, $consumerClass)
    {
        $this->createQueue();

        $this->createProducer($producerClass);
        $this->createConsumer($consumerClass);
    }

    /**
     * @return void
     */
    public function unSetQueue()
    {
        $this->queue = null;
        $this->producer = null;
        $this->consumer = null;
    }


    /**
     * @param QueueConfigDTO $config
     * @param                $producerClass
     * @param                $consumerClass
     *
     * @return QueueContext
     * @throws QueueException
     */
    public static function getInstance($config, $producerClass, $consumerClass)
    {
        $instance = self::readObjectFromFile($config->restartFilePath);
        if (!($instance instanceof self) || $config->force) {
            $instance = new self($config);
        }
        $instance->setUpQueue($producerClass, $consumerClass);

        return $instance;
    }

    /**
     * @return void
     * @throws QueueException
     */
    public function createContext()
    {
        if (!in_array($this->config->connectionType, $this->connectionTypes)) {
            throw new QueueException('Unknown connection type');
        }
        $connectionFactory = null;
        if ($this->config->connectionType === 'file') {
            if (!is_dir($this->config->fileConnectionPath)) {
                throw new QueueException('Incorrect connection file path');
            }
            $connectionFactory = new FsConnectionFactory($this->config->fileConnectionPath);
        }

        if ($connectionFactory) {
            $this->context = $connectionFactory->createContext();
        }

    }

    /**
     * @return void
     */
    public function createQueue()
    {
        $this->queue = $this->context->createQueue($this->config->queueData->name);
    }

    /**
     * @return void
     */
    public function purgeQueue()
    {
        if (file_exists($this->config->fileConnectionPath . '/' . $this->config->queueData->name)) {
            unlink($this->config->fileConnectionPath . '/' . $this->config->queueData->name);
        }
        if (file_exists($this->config->restartFilePath)) {
            unlink($this->config->restartFilePath);
        }
    }

    /**
     * @param $producerClass
     *
     * @return void
     */
    public function createProducer($producerClass)
    {
        $this->producer = new $producerClass($this);
    }

    /**
     * @param $consumerClass
     *
     * @return void
     */
    public function createConsumer($consumerClass)
    {
        $this->consumer = new $consumerClass($this);
    }

    /**
     * @param array $data
     *
     * @return void
     */
    public function enqueue($data)
    {
        if ($this->config->queueData->multipleItems) {
            $this->producer->enqueueMany($data);
        } else {
            $this->producer->enqueueOne($data);
        }
    }

    /**
     * @return void
     */
    public function dequeue($single = false)
    {
        if ($single) {
            $this->consumer->run();
        } else {
            $count = 1;
            while ($this->consumer->run()) {
                if ($this->ifRestartNeeded($count, $this->config->itemsCountForRestart)) {
                    $this->unSetQueue();
                    self::writeObjectInfile($this, $this->config->restartFilePath);
                    $this->restart($this->config->restartRoute, $this->config->restartBody);
                }
                $count++;
            }
            $this->purgeQueue();
            $this->consumer->finish();
        }
    }

    /**
     * @return Queue
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * @return QueueProducerInterface
     */
    public function getProducer()
    {
        return $this->producer;
    }

    /**
     * @return QueueConsumerInterface
     */
    public function getConsumer()
    {
        return $this->consumer;
    }

    /**
     * @return QueueDataDTO
     */
    public function getQueueData()
    {
        return $this->config->queueData;
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

}