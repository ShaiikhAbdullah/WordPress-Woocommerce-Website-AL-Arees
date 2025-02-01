<?php
namespace TenWebIO\Queue;

use TenWebIO\Compress;
use TenWebIO\Logs;
use TenWebIO\Utils;
use TenWebQueue\Abstracts\QueueConsumerAbstract;

class QueueConsumer extends QueueConsumerAbstract
{
    /**
     * @param $message
     *
     * @return bool
     */
    public function process($message)
    {
        $message_data = json_decode($message->getBody(), true);
        if (!is_array($message_data)) {
            $message_data = json_decode($message_data, true);
        }
        $compress = new Compress($this->getQueueName(), $message_data['guid'], $message_data['ID'], $message_data['size']);
        try {
            $compress->compress();
            Logs::setLog("compress:process:" . $message_data['guid'] . ':ID:' . $message_data['ID'], 'Success');
        } catch (\Exception $e) {
            Logs::setLog("compress:process:" . $message_data['guid'] . ':ID:' . $message_data['ID'], 'Error ' . $e->getMessage(), 'error');
            if ($e->getMessage() === 'finish_queue') {
                Logs::setLog("compress:process:finished", 'Queue has finished because of 400 bad request', 'error');
                $this->finishInCaseOfError();
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    public function finish()
    {
        Utils::finishQueue(Utils::getQueueTypeByName($this->getQueueName()));

        return true;
    }

    /**
     * @return bool
     */
    public function finishInCaseOfError()
    {
        Utils::finishQueue(Utils::getQueueTypeByName($this->getQueueName()), true, false);

        return true;
    }
}