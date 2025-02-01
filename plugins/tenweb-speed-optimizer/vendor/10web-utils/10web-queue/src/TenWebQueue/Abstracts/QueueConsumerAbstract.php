<?php
namespace TenWebQueue\Abstracts;

use Interop\Queue\Consumer;
use Interop\Queue\Message;
use TenWebQueue\Interfaces\QueueConsumerInterface;
use TenWebQueue\QueueContext;

abstract class QueueConsumerAbstract implements QueueConsumerInterface
{

    /**
     * @var QueueContext
     */
    private $queueContext;

    /**
     * @var Consumer
     */
    private $consumer;

    /**
     * @param QueueContext $context
     */
    public function __construct($context)
    {
        $this->queueContext = $context;
        $this->consumer = $this->queueContext->getContext()->createConsumer($context->getQueue());
    }

    /**
     * @param $requeue
     *
     * @return bool
     */
    public function run($requeue = false)
    {
        $message = $this->consumer->receive(1000);
        if ($message) {
            if ($this->process($message)) {
                $this->consumer->acknowledge($message);
            } else {
                $this->consumer->reject($message, $requeue);
            }

            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getQueueName()
    {
        return $this->queueContext->getQueue()->getQueueName();
    }

    /**
     * @param Message $message
     *
     * @return boolean
     */
    abstract public function process($message);


    /**
     * @return bool
     */
    abstract public function finish();
}