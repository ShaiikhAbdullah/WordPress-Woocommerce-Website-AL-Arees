<?php
namespace TenWebIO;

use TenWebIO\Exceptions\IOException;
use Tenweb_Authorization\Helper as AuthHelper;

class Api
{
    const API_COMPRESS_ACTION           = 'compress';
    const API_COMPRESS_CUSTOM_ACTION    = 'compress-custom';
    const API_COMPRESS_SETTINGS_ACTION  = 'compress_settings';
    const API_COMPRESS_LOG_STORE_ACTION = 'compress_log_store';
    const API_WEBP_CONVERT              = 'webp_convert';
    const API_DELETE_WEBP_CONVERTED     = 'delete_webp_converted';
    const API_GET_STAT                  = 'get_stat';
    const API_REPORT                    = 'report';

    private $auth;
    private $api_action = null;
    private $domain_id = 0;
    private $workspace_id = 0;

    private $response_status_code = 200;

    private static $post_headers_data = array(
        "Accept" => "application/x.10weboptimizer.v3+json"
    );

    /**
     * @param $api_action
     */
    public function __construct($api_action)
    {
        $this->auth = AuthHelper::get_instance();
        $this->api_action = $api_action;
        $this->setDomainId();
        $this->setWorkspaceId();
    }


    /**
     * @param string $method
     *
     * @param array  $request_data
     * @param array  $query
     * @param array  $headers
     *
     * @return array|false
     */
    public function apiRequest($method = "GET", $request_data = array(), $query = array(), $headers = array())
    {
        try {
            if (Utils::ifWorkspaceOrDomainEmpty()) {
                return false;
            }
            $data["body"] = $request_data;
            $data["headers"] = $headers + self::$post_headers_data;
            $data["headers"]["Authorization"] = get_site_option(TENWEBIO_MANAGER_PREFIX . '_access_token');
            $data["method"] = $method;
            $data["timeout"] = 50000;

            $url = $this->getApiUrl($query);

            Logs::setLog("api:" . $url . ":" . $method . ":request-body", $data);

            //$this->auth->request($url, $data);
            //$response = $this->auth->last_response;
            $response = wp_remote_request($url, $data);

            $this->response_status_code = wp_remote_retrieve_response_code($response);
            if (is_wp_error($response) || $this->response_status_code !== 200 || empty($response['body'])) {
                Logs::setLog("api:" . $url . ":" . $method . ":response-error-status", wp_remote_retrieve_response_code($response), 'error');
                Logs::setLog("api:" . $url . ":" . $method . ":response-error", $response, 'error');

                return false;
            }
            Logs::setLog("api:" . $url . ":" . $method . ":response-body", $response['body']);
            $response = json_decode($response['body'], true);

            return !empty($response['data']) ? $response['data'] : false;

        } catch (\Exception $e) {
            Logs::setLog("api:" . $this->api_action . ":" . $method . ":exception", $e->getMessage(), 'error');
        }

        return false;
    }

    /**
     * @return int
     */
    public function getResponseStatusCode()
    {
        return $this->response_status_code;
    }

    /**
     * @param array $query
     *
     * @return string
     * @throws IOException
     */
    private function getApiUrl($query = array())
    {
        $api_action_url_map = $this->getActionUrlMap();
        if (empty($api_action_url_map[$this->api_action])) {
            throw new IOException('Wrong api action');
        }
        $api_url = $api_action_url_map[$this->api_action];
        if (!empty($query)) {
            $api_url = $api_url . '?' . http_build_query($query);
        }

        return $api_url;
    }

    /**
     * @return string[]
     */
    private function getActionUrlMap()
    {
        return array(
            self::API_COMPRESS_ACTION           => TENWEBIO_API_URL . '/compress/workspaces/' . $this->workspace_id . '/domains/' . $this->domain_id . '/connected/optimize',
            self::API_COMPRESS_CUSTOM_ACTION    => TENWEBIO_API_URL . '/compress/workspaces/' . $this->workspace_id . '/domains/' . $this->domain_id . '/connected/optimize-custom',
            self::API_COMPRESS_SETTINGS_ACTION  => TENWEBIO_API_URL . '/compress/workspaces/' . $this->workspace_id . '/domains/' . $this->domain_id . '/optimize/data',
            self::API_COMPRESS_LOG_STORE_ACTION => TENWEBIO_API_URL . '/compress/workspaces/' . $this->workspace_id . '/domains/' . $this->domain_id . '/logs/store',
            self::API_WEBP_CONVERT              => TENWEBIO_API_URL . '/compress/workspaces/' . $this->workspace_id . '/domains/' . $this->domain_id . '/convert-to-webp',
            self::API_DELETE_WEBP_CONVERTED     => TENWEBIO_API_URL . '/compress/workspaces/' . $this->workspace_id . '/domains/' . $this->domain_id . '/delete-converted-webp',
            self::API_GET_STAT                  => TENWEBIO_API_URL . '/compress/workspaces/' . $this->workspace_id . '/domains/' . $this->domain_id . '/stat',
            self::API_REPORT                    => TENWEBIO_API_URL . '/compress/workspaces/' . $this->workspace_id . '/domains/' . $this->domain_id . '/report',
        );
    }

    /**
     * @return void
     */
    private function setDomainId()
    {
        $this->domain_id = (int)get_option(TENWEBIO_MANAGER_PREFIX . '_domain_id', 0);
    }

    /**
     * @return void
     */
    private function setWorkspaceId()
    {
        $this->workspace_id = (int)get_site_option(TENWEBIO_MANAGER_PREFIX . '_workspace_id', 0);
    }
}
