<?php
namespace TenWebQueue\Abstracts;

use Interop\Queue\Exception;
use Interop\Queue\Exception\InvalidDestinationException;
use Interop\Queue\Exception\InvalidMessageException;
use Interop\Queue\Producer;
use TenWebQueue\Interfaces\QueueProducerInterface;
use TenWebQueue\QueueContext;

abstract class QueueProducerAbstract implements QueueProducerInterface
{
    /**
     * @var QueueContext
     */
    private $queueContext;

    /**
     * @var Producer
     */
    private $producer;


    /**
     * @param QueueContext $context
     */
    public function __construct($context)
    {
        $this->queueContext = $context;
        $this->producer = $this->queueContext->getContext()->createProducer();
    }

    /**
     * @param array $data
     *
     * @return void
     * @throws Exception
     * @throws InvalidDestinationException
     * @throws InvalidMessageException
     */
    public function enqueueOne($data = array())
    {
        $this->enqueue(json_encode($data));
    }

    /**
     * @param array $data
     *
     * @return void
     * @throws Exception
     * @throws InvalidDestinationException
     * @throws InvalidMessageException
     */
    public function enqueueMany($data = array())
    {
        foreach ($data as $item) {
            $this->enqueue(json_encode($item));
        }
    }

    /**
     * @param $data
     *
     * @return void
     * @throws Exception
     * @throws InvalidDestinationException
     * @throws InvalidMessageException
     */
    public function enqueue($data)
    {
        $message = $this->queueContext->getContext()->createMessage(json_encode($data));
        $this->producer->send($this->queueContext->getQueue(), $message);
    }
}