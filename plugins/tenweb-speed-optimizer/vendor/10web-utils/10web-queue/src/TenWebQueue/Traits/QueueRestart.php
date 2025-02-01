<?php
namespace TenWebQueue\Traits;

trait QueueRestart
{
    /**
     * @var float $startTime
     */
    private $startTime = 0;

    /**
     * @param $itemsCount
     * @param $itemsAllowedCount
     *
     * @return bool
     */
    public function ifRestartNeeded($itemsCount, $itemsAllowedCount = 20)
    {
        $maxExecTime = ini_get('max_execution_time');
        $scriptExecTime = microtime(true) - $this->getStartTime();

        if ($scriptExecTime >= (int)$maxExecTime - 10 || $itemsCount >= $itemsAllowedCount) {

            return true;
        }

        return false;
    }

    public function restart($route, $body)
    {
        /*$ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $route);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0.1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0.1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('restart' => 1)));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);

        curl_close($ch);*/
        //todo change this to curl
        try {
            $route = $route . '&c=' . rand(1, 5000);
            wp_remote_post($route, array('method' => 'POST', 'sslverify' => false, 'timeout' => 0.1, 'body' => $body));

        } catch (\Exception $e) {

        }

        die('{"tenweb_queue_restart": "1"}');
    }

    /**
     * @param $object
     * @param $path
     *
     * @return void
     */
    public static function writeObjectInfile($object, $path)
    {
        $content = serialize($object);
        file_put_contents($path, $content);
    }

    /**
     * @param $path
     *
     * @return mixed|null
     */
    public static function readObjectFromFile($path)
    {
        if (is_file($path)) {
            return unserialize(file_get_contents($path));
        }

        return null;
    }

    /**
     * @param $path
     *
     */
    public static function deleteObjectFile($path)
    {
        if (is_file($path)) {
            unlink($path);
        }
    }

    /**
     * @return float
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @param float $startTime
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
    }
}
