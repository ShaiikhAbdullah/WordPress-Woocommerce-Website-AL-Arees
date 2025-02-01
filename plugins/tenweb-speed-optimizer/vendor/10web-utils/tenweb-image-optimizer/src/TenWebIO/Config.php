<?php
namespace TenWebIO;

class Config
{
    private $debug_mode = 0;
    private $images_limit_for_restart = 20;
    private $auto_optimize_with_rest = 1;
    private $queue_chunk_images_limit = 1000;
    private $stat_chunk_images_limit = 500000;
    private $compress_only_full_size = 0;

    public function __construct()
    {
        $config = get_site_option(TENWEBIO_PREFIX . '_configs');
        if (!empty($config['debug_mode'])) {
            $this->debug_mode = (int)$config['debug_mode'];
        } else {
            $this->debug_mode = 0;
        }
        if (!empty($config['auto_optimize_with_rest'])) {
            $this->auto_optimize_with_rest = (int)$config['auto_optimize_with_rest'];
        } else {
            $this->auto_optimize_with_rest = 1;
        }

        if (!empty($config['images_limit_for_restart'])) {
            $this->images_limit_for_restart = (int)$config['images_limit_for_restart'];
        }

        if (!empty($config['queue_chunk_images_limit'])) {
            $this->queue_chunk_images_limit = (int)$config['queue_chunk_images_limit'];
        }

        if (!empty($config['stat_chunk_images_limit'])) {
            $this->stat_chunk_images_limit = (int)$config['stat_chunk_images_limit'];
        }
        if (!empty($config['compress_only_full_size'])) {
            $this->compress_only_full_size = (int)$config['compress_only_full_size'];
        } else {
            $this->compress_only_full_size = 0;
        }
    }

    public function getDebugMode()
    {
        return $this->debug_mode;
    }

    public function getImagesLimitForRestart()
    {
        return $this->images_limit_for_restart;
    }

    public function getAutoOptimizeWithRest()
    {
        return $this->auto_optimize_with_rest;
    }

    public function getQueueChunkImagesLimit()
    {
        return $this->queue_chunk_images_limit;
    }

    public function getStatChunkImagesLimit()
    {
        return $this->stat_chunk_images_limit;
    }

    public function getCompressOnlyFullSize()
    {
        return $this->compress_only_full_size;
    }

    public function save($data)
    {
        if (isset($data['debug_mode'])) {
            $this->debug_mode = 1;
        } else {
            $this->debug_mode = 0;
        }
        if (isset($data['images_limit_for_restart'])) {
            $this->images_limit_for_restart = (int)$data['images_limit_for_restart'];
        }
        if (isset($data['auto_optimize_with_rest'])) {
            $this->auto_optimize_with_rest = 1;
        } else {
            $this->auto_optimize_with_rest = 0;
        }
        if (isset($data['queue_chunk_images_limit'])) {
            $this->queue_chunk_images_limit = (int)$data['queue_chunk_images_limit'];
        }
        if (isset($data['stat_chunk_images_limit'])) {
            $this->stat_chunk_images_limit = (int)$data['stat_chunk_images_limit'];
        }
        if (isset($data['compress_only_full_size'])) {
            $this->compress_only_full_size = 1;
        } else {
            $this->compress_only_full_size = 0;
        }
        update_site_option(TENWEBIO_PREFIX . '_configs', array(
            'debug_mode'               => $this->debug_mode,
            'images_limit_for_restart' => $this->images_limit_for_restart,
            'auto_optimize_with_rest'  => $this->auto_optimize_with_rest,
            'queue_chunk_images_limit' => $this->queue_chunk_images_limit,
            'stat_chunk_images_limit'  => $this->stat_chunk_images_limit,
            'compress_only_full_size'  => $this->compress_only_full_size,
        ));
    }

}