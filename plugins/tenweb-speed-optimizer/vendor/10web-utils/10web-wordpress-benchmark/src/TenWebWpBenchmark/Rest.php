<?php

namespace TenWebWpBenchmark;

use \WP_REST_Request;
use \WP_Error;
use \Exception;
use \WP_REST_Response;
use \WP_REST_Controller;

class Rest extends WP_REST_Controller
{

    public const TWO_BENCHMARK_API_NAMESPACE = 'tenweb_so/v1';
    public function registerRoutes()
    {
        register_rest_route(self::TWO_BENCHMARK_API_NAMESPACE, 'get_benchmark_data',
            array(
                'methods'  => 'GET',
                'permission_callback' => array($this, 'check_authorization'),
                'callback' => array($this, "get_benchmark_data"),
            )
        );

        register_rest_route(self::TWO_BENCHMARK_API_NAMESPACE, 'rerun_benchmark',
            array(
                'methods'  => 'POST',
                'permission_callback' => array($this, 'check_authorization'),
                'callback' => array($this, "rerun_benchmark"),
            )
        );
    }

    private function clear_response() {
        // Clear all unexpected output. We don't want to see a warning in rest response.
        while (ob_get_level() !== 0) {
            ob_end_clean();
        }
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function get_benchmark_data(WP_REST_Request $request) {
        $this->clear_response();
        $data_for_response = array(
            'success'=>false,
            'message'=>"No Benchmark Data",
        );
        try {
            $benchmark = \TenWebWpBenchmark\OptimizerBenchmark::get_instance();
            if(isset($benchmark) && $benchmarkData = $benchmark->getData()){
                $data_for_response = [
                    'success' => true,
                    'message' => 'Success',
                    'data' => $benchmarkData,
                ];
            }
        } catch (Exception $exception) {
            $data_for_response['message'] = 'Error in getting benchmark data';
            $data_for_response['error'] = $exception->getMessage().' in '.$exception->getFile().' on '.$exception->getLine();

            return new WP_REST_Response($data_for_response, 500);
        }

        return new WP_REST_Response($data_for_response, 200);
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function rerun_benchmark(WP_REST_Request $request) {
        $this->clear_response();
        $data_for_response = array(
            'success'=>false,
            'message'=>"Benchmark failed",
        );
        try {
            $benchmark = \TenWebWpBenchmark\OptimizerBenchmark::get_instance();
            if(isset($benchmark) && $benchmarkData = $benchmark->test()){
                $data_for_response = [
                    'success' => true,
                    'message' => 'Success',
                    'data' => $benchmarkData,
                ];
            }
        } catch (Exception $exception) {
            $data_for_response['message'] = 'Error in rerunning benchmark';
            $data_for_response['error'] = $exception->getMessage().' in '.$exception->getFile().' on '.$exception->getLine();

            return new WP_REST_Response($data_for_response, 500);
        }

        return new WP_REST_Response($data_for_response, 200);
    }

    public function check_authorization(WP_REST_Request $request){
        if (!\Tenweb_Authorization\Login::get_instance()->check_logged_in()){
            $data_for_response = array(
                "code"    => "unauthorized",
                "message" => "unauthorized",
                "data"    => array(
                    "status" => 401
                )
            );
            return new WP_Error('rest_forbidden', $data_for_response, 401);
        }
        $authorize = \Tenweb_Authorization\Login::get_instance()->authorize($request);
        if (is_array($authorize)) {
            return new WP_Error('rest_forbidden', $authorize, 401);
        }
        return true;
    }
}

