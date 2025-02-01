<?php

namespace TenWebIO;

class Logs
{
    /**
     * @param        $key
     * @param        $msg
     * @param string $type // log, error
     *
     * @return void
     */
    public static function setLog($key, $msg, $type = 'log')
    {
        $config = new Config();
        if (empty($config->getDebugMode()) && $type != 'error') {
            return;
        }
        $logs = self::getLogs();

        // add log to the beginning of existing logs
        if (!(is_string($msg) || is_numeric($msg))) {
            $msg = json_encode($msg);
        }
        if (count($logs) >= 50) {
            Logs::clearLogs();
        }
        $logs[$key] = array('msg' => $msg, 'date' => date('Y-m-d H:i:s'), 'type' => $type);
        $expiration = 6 * 60 * 60;
        set_transient(TENWEBIO_PREFIX . '_logs', $logs, $expiration);
    }

    /**
     * @return array
     */
    public static function getLogs()
    {
        $logs = get_transient(TENWEBIO_PREFIX . '_logs');
        if (!is_array($logs)) {
            $logs = array();
        }

        return $logs;
    }

    public static function clearLogs()
    {
        delete_transient(TENWEBIO_PREFIX . '_logs');
    }
}
