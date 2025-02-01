<?php
namespace TenWebQueue\Interfaces;

interface QueueProducerInterface
{
    /**
     * @return void
     **/
    public function enqueueOne();

    /**
     * @return void
     **/
    public function enqueueMany();
}