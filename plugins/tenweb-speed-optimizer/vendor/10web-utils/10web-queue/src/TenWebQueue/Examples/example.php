<?php

use TenWebQueue\QueueOrchestrator;
use TenWebQueue\DTO\QueueConfigDTO;
use TenWebQueue\DTO\QueueDataDTO;

$name = "test_queue";

$q = QueueOrchestrator::initQueue(new QueueConfigDTO([
    'connection_type'         => 'file',
    'file_connection_path'    => ABSPATH . 'wp-content',
    'restart_file_path'       => '/tenweb_image_optimizer_restart.txt',
    'restart_route'           => 'tenwebio/v2/compress',
    'items_count_for_restart' => 20,
    'force'                   => false,
    'queue_data'              => new QueueDataDTO([
        'name'           => $name,
        'data'           => [["post_id" => 1, "url" => "example1"], ["post_id" => 2, "url" => "example2"], ["post_id" => 3, "url" => "example3"]],
        'multiple_items' => true,
    ])
]), \TenWebQueue\Examples\QueueProducer::class, \TenWebQueue\Examples\QueueConsumer::class);
$q->enqueue();

$q->dequeue(true);
