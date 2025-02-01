<?php
namespace TenWebIO;

class CLI
{
    public static function readyToOptimizeImages($args, $assoc_args)
    {
        $blog_id = $args[0];
        if (is_multisite()) {
            switch_to_blog($blog_id);
        }
        $io_compress_data = new CompressDataService();
        $images = $io_compress_data->getImagesReadyForOptimize(false, true)->getData();
        if (empty($images)) {
            \WP_CLI::error('No images for optimization.');
        } else {
            \WP_CLI\Utils\format_items(
                'json',
                $images,
                ['ID', 'guid', 'size']
            );
        }
        if (is_multisite()) {
            restore_current_blog();
        }
    }

    public static function storeOptimizedImagesLog($args, $assoc_args)
    {
        $blog_id = $args[0];
        $data = $args[1];
        $is_finished = $args[2];

        if (is_multisite()) {
            switch_to_blog($blog_id);
        }
        if ($data) {
            $data = json_decode($data, true);
        } else {
            $data = array();
        }
        $data = array_map(function ($value) {
            if (!is_array($value)) {
                return json_decode($value);
            }

            return $value;
        }, $data);
        $io_compress = new CompressService();
        // store data in service
        $io_compress->storeDataInService($data, $is_finished);

        \WP_CLI::success('ok');
        if (is_multisite()) {
            restore_current_blog();
        }
    }

    public static function storeLastOptimizationLog($args, $assoc_args)
    {
        $blog_id = $args[0];
        $image_orig_size = $args[1];
        $image_size = $args[2];
        $count = $args[3];

        if (is_multisite()) {
            switch_to_blog($blog_id);
        }

        $last_compress = new LastCompress(true);
        $last_compress->update($image_size, $image_orig_size, $count);

        \WP_CLI::success('ok');
        if (is_multisite()) {
            restore_current_blog();
        }
    }

    public static function convertedImages($args, $assoc_args)
    {
        $counter = $args[0] ? $args[0] : '1';
        $glue = $args[1] ? $args[1] : ' ';
        $webP_paths = isset($args[2]) ? (int)$args[2] : 1;
        $io_compress = new CompressService();
        $images = $io_compress->getAlreadyConvertedImagesPaths($counter, $webP_paths);
        if (empty($images)) {
            \WP_CLI::error('Counter ' . $counter . '. No converted images.');
        } else {
            \WP_CLI::line(implode($glue, $images));
        }
    }
}


