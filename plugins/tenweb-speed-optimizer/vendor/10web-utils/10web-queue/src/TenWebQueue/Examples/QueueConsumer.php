<?php
namespace TenWebQueue\Examples;


use TenWebQueue\Abstracts\QueueConsumerAbstract;

class QueueConsumer extends QueueConsumerAbstract
{
    public function process($message)
    {
        var_dump($message);

        return true;
    }

    public function finish()
    {
        return true;
    }
}