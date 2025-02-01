<?php
namespace TenWebQueue;

use TenWebQueue\DTO\QueueConfigDTO;
use TenWebQueue\Exceptions\QueueException;

class QueueOrchestrator
{
    /**
     * @param QueueConfigDTO $config
     * @param                $producerClass
     * @param                $consumerClass
     *
     * @return QueueContext
     * @throws QueueException
     */
    public static function initQueue($config, $producerClass, $consumerClass)
    {
        return QueueContext::getInstance($config, $producerClass, $consumerClass);
    }

}