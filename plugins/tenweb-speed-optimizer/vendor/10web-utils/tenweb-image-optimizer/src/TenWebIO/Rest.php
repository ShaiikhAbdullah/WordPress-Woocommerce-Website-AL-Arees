<?php

namespace TenWebIO;

use Tenweb_Authorization\Helper;

class Rest extends \WP_REST_Controller
{
    private $version = '2';
    private $route = 'tenwebio';
    private $status = 404;
    private $response = array();

    private $bases = array(
        'compress-custom' => array('route'   => '/compress-custom/',
                                   'methods' => \WP_REST_Server::CREATABLE,
                                   'args'    => array(
                                       'restart'           => array(
                                           'required' => false,
                                           'type'     => 'int',
                                       ),
                                       'image_urls'        => array(
                                           'required' => false,
                                           'type'     => 'array',
                                       ),
                                       'page_id'           => array(
                                           'required' => false,
                                           'type'     => 'string',
                                       ),
                                       'only_convert_webp' => array(
                                           'required' => false,
                                           'type'     => 'number',
                                       ),
                                       'origin'            => array(
                                           'required' => false,
                                           'type'     => 'string',
                                       ),
                                   )),
        'compress-one'    => array('route'   => '/compress-one/',
                                   'methods' => \WP_REST_Server::CREATABLE,
                                   'args'    => array(
                                       'id' => array(
                                           'required' => true,
                                           'type'     => 'int',
                                       ),
                                   )),
        'compress'        => array('route'   => '/compress/',
                                   'methods' => \WP_REST_Server::CREATABLE,
                                   'args'    => array(
                                       'restart' => array(
                                           'required' => false,
                                           'type'     => 'int',
                                       ),
                                   )),
        'logs'            => array('route'   => '/logs/',
                                   'methods' => \WP_REST_Server::READABLE,
                                   'args'    => array()),
        'ping'            => array('route'   => '/ping/',
                                   'methods' => \WP_REST_Server::READABLE,
                                   'args'    => array()),
        'clear-cache'     => array('route'   => '/clear-cache/',
                                   'methods' => \WP_REST_Server::READABLE,
                                   'args'    => array()),
        'restore-backup'  => array('route'   => '/restore-backup/',
                                   'methods' => \WP_REST_Server::CREATABLE,
                                   'args'    => array(
                                       'type' => array(
                                           'required' => false,
                                           'type'     => 'string',
                                       ),
                                   )),
        'delete-backup'   => array('route'   => '/delete-backup/',
                                   'methods' => \WP_REST_Server::CREATABLE,
                                   'args'    => array(
                                       'type' => array(
                                           'required' => false,
                                           'type'     => 'string',
                                       ),
                                   )),

    );


    public function registerRoutes()
    {
        $namespace = $this->route . '/v' . $this->version;
        foreach ($this->bases as $base => $route_data) {
            register_rest_route($namespace, $route_data['route'], array(
                array(
                    'methods'             => $route_data['methods'],
                    'callback'            => array($this, 'callback'),
                    'permission_callback' => array($this, 'checkAuthorization'),
                    'args'                => $route_data['args']
                )
            ));
        }
    }

    public function checkAuthorization($request)
    {
        $route = $request->get_route();
        $endpoint = $this->parseEndpoint($route);
        if ($endpoint == 'ping') {
            return true;
        }
        $unauthorized = false;
        $unauthorized_response = array(
            "code"    => "unauthorized",
            "message" => "",
            "data"    => array(
                "status" => 401
            )
        );

        if (Utils::ifWorkspaceOrDomainEmpty()) {
            $unauthorized = true;
            $unauthorized_response = array(
                "code"    => "empty_workspace",
                "message" => "Empty workspace or domain.",
                "data"    => array(
                    "status" => 401
                )
            );
        } else {
            if (!empty($request->get_param('tenwebio_nonce'))) {
                if (!check_ajax_referer('tenwebio_rest', 'tenwebio_nonce', false)) {
                    $unauthorized = true;
                    $unauthorized_response = array(
                        "code"    => "wrong_nonce",
                        "message" => "Wrong nonce.",
                        "data"    => array(
                            "status" => 401
                        )
                    );
                }
            } else {
                $auth = Helper::get_instance();
                $token = $request->get_header('tenweb-authorization');
                if (empty($token) || $auth->check_single_token($token) === false) {
                    $unauthorized = true;
                    $unauthorized_response = array(
                        "code"    => "unauthorized",
                        "message" => "unauthorized, please login",
                        "data"    => array(
                            "status" => 401
                        )
                    );
                }
            }
        }

        if ($unauthorized) {
            Logs::setLog("rest:unauthorized", $unauthorized_response, 'error');

            return false;
        }

        return true;
    }

    /**
     * @param $request
     *
     * @return \WP_REST_Response
     */
    public function callback($request)
    {
        $route = $request->get_route();
        $endpoint = $this->parseEndpoint($route);
        try {
            switch ($endpoint) {
                case 'compress':
                    $this->compress($request);
                    break;
                case 'compress-one':
                    $this->compressOne($request);
                    break;
                case 'compress-custom'  :
                    $this->compressCustom($request);
                    break;
                case 'logs':
                    $this->logs($request);
                    break;
                case 'ping':
                    $this->ping($request);
                    break;
                case 'clear-cache':
                    $this->clearCache($request);
                    break;
                case 'restore-backup':
                    $this->restoreBackup($request);
                    break;
                case 'delete-backup':
                    $this->deleteBackup($request);
                    break;
                default:
                    $this->status = 404;
                    $this->response = array(
                        "code"    => "rest_no_route",
                        "message" => "No route was found matching the URL and request method.",
                        "data"    => array(
                            "status" => 404
                        )
                    );
                    break;
            }
        } catch (\Exception $e) {
            $this->status = 500;
            $this->response = array(
                "code"    => "error",
                "message" => $e->getMessage(),
                "data"    => array(
                    "status" => 500
                )
            );
        }
        Logs::setLog("rest:" . $endpoint . ":log", array(
            'action'   => $endpoint,
            'status'   => $this->status,
            'response' => $this->response,
        ));

        return new \WP_REST_Response($this->response, $this->status);
    }


    /**
     * @param $request
     *
     * @return void
     * @throws \TenWebQueue\Exceptions\QueueException
     */
    private function compress($request)
    {
        $restart = false;
        $force = false;
        if ($request->get_param('restart')) {
            $restart = true;
        }
        if ($request->get_param('force')) {
            $force = true;
        }
        try {
            $compress = new CompressService($restart);
            if ($compress->compressBulk($force)) {
                $this->status = 201;
                $this->response = array(
                    "status"  => "201",
                    "message" => "ok",
                );
            } else {
                $this->status = 400;
            }

        } catch (\Exception $e) {
            $this->status = 500;
            $this->response = array(
                "status"  => 500,
                "message" => $e->getMessage(),
            );
        }
    }

    /**
     * @param $request
     *
     * @return void
     * @throws \TenWebQueue\Exceptions\QueueException
     */
    private function compressOne($request)
    {
        $restart = false;
        if ($request->get_param('restart')) {
            $restart = true;
        }
        try {
            $compress = new CompressService($restart);
            $compress->compressOne($request->get_param('id'));
            $this->status = 201;
            $this->response = array(
                "status"  => "201",
                "message" => "ok",
            );
        } catch (\Exception $e) {
            $this->status = 500;
            $this->response = array(
                "status"  => 500,
                "message" => $e->getMessage(),
            );
        }
    }

    /**
     * @param $request
     *
     * @return void
     */
    private function compressCustom($request)
    {
        $restart = false;
        if ($request->get_param('restart')) {
            $restart = true;
        }
        $image_urls = $request->get_param('image_urls');
        $page_id = $request->get_param('page_id');
        $only_convert_webp = (int)$request->get_param('only_convert_webp');
        $origin = $request->get_param('origin');
        try {
            $compress = new CompressService($restart);
            $compress->compressCustom($image_urls, $page_id, $only_convert_webp, $origin);
            $this->status = 201;
            $this->response = array(
                "status"  => "201",
                "message" => "ok",
            );
        } catch (\Exception $e) {
            $this->status = 500;
            $this->response = array(
                "status"  => 500,
                "message" => $e->getMessage(),
            );
        }
    }

    /**
     * @param $request
     *
     * @return void
     */
    private function logs($request)
    {
        try {
            $compress_data = new CompressDataService();
            $this->status = 200;
            $this->response = array(
                "status"  => 200,
                "message" => "ok",
                "data"    => $compress_data->getNotCompressedNumbers()
            );
        } catch (\Exception $e) {
            $this->status = 500;
            $this->response = array(
                "status"  => 500,
                "message" => $e->getMessage(),
            );
        }
    }

    /**
     * @param $request
     *
     * @return void
     */
    private function ping($request)
    {
        try {
            $this->status = 200;
            $this->response = array(
                "status"  => 200,
                "message" => "ok"
            );
        } catch (\Exception $e) {
            $this->status = 500;
            $this->response = array(
                "status"  => 500,
                "message" => $e->getMessage(),
            );
        }
    }

    /**
     * @param $request
     *
     * @return void
     */
    private function clearCache($request)
    {
        try {
            Settings::purgeCompressSettings();
            $compress_settings = new Settings();
            $compress_settings->getSettings(true, 1, 1);
            $this->status = 200;
            $this->response = array(
                "status"  => 200,
                "message" => "ok"
            );
        } catch (\Exception $e) {
            $this->status = 500;
            $this->response = array(
                "status"  => 500,
                "message" => $e->getMessage(),
            );
        }
    }

    /**
     * @param $request
     *
     * @return void
     */
    private function restoreBackup($request)
    {
        try {
            $type = $request->get_param('type') ? $request->get_param('type') : 'bulk';
            $backup = new Backup($type);
            $backup->restoreOriginalImages();
            $this->status = 200;
            $this->response = array(
                "status"  => 200,
                "message" => "ok"
            );
        } catch (\Exception $e) {
            $this->status = 500;
            $this->response = array(
                "status"  => 500,
                "message" => $e->getMessage(),
            );
        }
    }

    /**
     * @param $request
     *
     * @return void
     */
    private function deleteBackup($request)
    {
        try {
            $type = $request->get_param('type') ? $request->get_param('type') : 'bulk';
            $backup = new Backup($type);
            $backup->deleteBackup();
            $this->status = 200;
            $this->response = array(
                "status"  => 200,
                "message" => "ok"
            );
        } catch (\Exception $e) {
            $this->status = 500;
            $this->response = array(
                "status"  => 500,
                "message" => $e->getMessage(),
            );
        }
    }

    /**
     * @param $route
     *
     * @return int|null|string
     */
    private function parseEndpoint($route)
    {
        $route_url = substr($route, 9);
        foreach ($this->bases as $key => $value) {
            $route_regex = '/' . $key . '/';
            if (preg_match($route_regex, $route_url)) {
                return $key;
            }
        }

        return null;
    }
}

