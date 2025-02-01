<?php

namespace TenWebWpBenchmark;


class Init
{
    private static $instance = null;

    private function __construct()
    {
        $this->initRest();
    }

    /**
     * @return Init|null
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            return new self();
        }

        return self::$instance;
    }


    /**
     * @return void Rest.php
     */
    private function initRest()
    {
        if (class_exists("\WP_REST_Controller")) {
            add_action('rest_api_init', function () {
                $rest = new \TenWebWpBenchmark\Rest();
                $rest->registerRoutes();
            });
        }
    }
}