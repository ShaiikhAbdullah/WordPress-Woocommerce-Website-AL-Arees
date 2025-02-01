<?php
namespace TenWebQueue\Interfaces;

use Interop\Queue\Message;

interface QueueConsumerInterface
{
    /**
     * @param $requeue
     *
     * @return bool
     */
    public function run($requeue = false);

    /**
     * @param Message $message
     *
     * @return boolean
     */
    public function process($message);

    /**
     *
     * @return boolean
     */
    public function finish();
}