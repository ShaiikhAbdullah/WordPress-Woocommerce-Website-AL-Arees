<?php

namespace TenWebOptimizer;

/*
 * Handles disk-cache-related operations.
 */
if (!defined('ABSPATH')) {
    exit;
}

class OptimizerCache
{
    /**
     * Cache filename.
     *
     * @var string
     */
    private $filename;

    /**
     * Cache directory path (with a trailing slash).
     *
     * @var string
     */
    private $cachedir;

    /**
     * Whether gzipping is done by the web server or us.
     * True => we don't gzip, the web server does it.
     * False => we do it ourselves.
     *
     * @var bool
     */
    private $nogzip;

    private $hashes;

    private $ext;

    private $media;

    /**
     * Ctor.
     *
     * @param string $md5 hash
     * @param string $ext extension
     */
    public function __construct($post_id = null, $ext = 'php', $media = 'all', $name_prefix = '')
    {
        if (!isset($post_id)) {
            $post_id = OptimizerUtils::get_current_post_info();
        }

        if (empty($name_prefix)) {
            $name_prefix = 'aggregated';
        }
        $this->media = $media;
        $this->cachedir = TWO_CACHE_DIR;
        $this->nogzip = TWO_CACHE_NOGZIP;
        $this->ext = $ext;

        if (!$this->nogzip) {
            $this->filename = TWO_CACHEFILE_PREFIX . $post_id . 'gzip.php';
        } else {
            if (in_array($ext, ['js', 'css'])) {
                if ($media !== 'all') {
                    if (!empty($media)) {
                        $name_prefix .= '_' . md5($media) . '_delay';
                    } else {
                        $name_prefix .= '_delay';
                    }
                }
                $this->filename = $ext . '/' . TWO_CACHEFILE_PREFIX . $post_id . '_' . $name_prefix . '.min.' . $ext;
            } elseif ($ext === 'critical') {
                $this->filename = $ext . '/' . TWO_CACHEFILE_PREFIX . $post_id . '_' . $name_prefix . '.css';
            } elseif ($ext === 'font') {
                $this->filename = 'critical/' . TWO_CACHEFILE_PREFIX . $post_id . '_' . $name_prefix . '.json';
            } else {
                $this->filename = TWO_CACHEFILE_PREFIX . $post_id . '.' . $ext;
            }
        }
    }

    //todo remove first version cache logic

    /**
     * Check whether it is a GET REQUEST
     */
    public static function isGetRequest()
    {
        return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * Returns true if the cached file exists on disk.
     *
     * @return bool
     */
    public function check()
    {
        static $files = [];
        $files[] = $this->filename;

        if (
            !is_dir($this->cachedir) &&
            !mkdir($concurrentDirectory = $this->cachedir, 0777, true) && // phpcs:ignore
            !is_dir($concurrentDirectory) &&
            !is_writable($concurrentDirectory) // phpcs:ignore
        ) {
            return false;
        }
        file_put_contents($this->cachedir . '_all_cache_files.txt', json_encode($files)); // phpcs:ignore

        return file_exists($this->cachedir . $this->filename);
    }

    /**
     * Returns cache contents if they exist, false otherwise.
     *
     * @return string|false
     */
    public function retrieve()
    {
        if ($this->check()) {
            if (false == $this->nogzip) {
                return file_get_contents($this->cachedir . $this->filename . '.none');
            }

            return file_get_contents($this->cachedir . $this->filename); // phpcs:ignore
        }

        return false;
    }

    /**
     * Stores given $data in cache.
     *
     * @param string $data data to cache
     * @param string $mime mimetype
     *
     * @return void
     */
    public function cache($data, $mime)
    {
        self::check_and_create_dirs();

        if ($this->nogzip === false) {
            // We handle gzipping ourselves.
            $file = 'default.php';
            $phpcode = file_get_contents(TENWEB_SO_PLUGIN_DIR . 'config/' . $file);
            $phpcode = str_replace(['%%CONTENT%%', 'exit;'], [$mime, ''], $phpcode);
            @file_put_contents($this->cachedir . $this->filename, $phpcode); // phpcs:ignore
            @file_put_contents($this->cachedir . $this->filename . '.none', $data); // phpcs:ignore
        } else {
            // Write code to cache without doing anything else.
            @file_put_contents($this->cachedir . $this->filename, $data); // phpcs:ignore
        }

        if (!empty($this->hashes)) {
            $cacheFile = $this->cachedir . '_cached.json';
            $oldData = '';

            if (file_exists($cacheFile)) {
                $oldData = json_decode(file_get_contents($cacheFile), true); // phpcs:ignore
            }

            if (empty($oldData)) {
                $oldData = [];
            }
            $oldData[$this->filename] = ['media' => $this->media, 'hashes' => $this->hashes];
            @file_put_contents($cacheFile, json_encode($oldData)); // phpcs:ignore
        }
    }

    public static function getFileCacheSructure()
    {
        $cacheFile = TWO_CACHE_DIR . '_cached.json';

        if (file_exists($cacheFile)) {
            return json_decode(file_get_contents($cacheFile), true); // phpcs:ignore
        }

        return [];
    }

    public static function filterThroughCache($scripts)
    {
        $cachedFiles = OptimizerCache::getFileCacheSructure();

        $result = [
            'code' => [],
            'scripts' => $scripts
        ];

        foreach ($cachedFiles as $key => $files) {
            $scriptsToRemove = [];

            if (isset($files['hashes'])) {
                foreach ($files['hashes'] as $i => $file) {
                    if (isset($scripts[$file])) {
                        $scriptsToRemove[] = $file;
                        unset($files['hashes'][$i]);
                    }
                }

                if (empty($files['hashes'])) {
                    if (empty($result['code'][$files['media']])) {
                        $result['code'][$files['media']] = '';
                    }
                    $result['code'][$files['media']] .= file_get_contents(TWO_CACHE_DIR . $key); // phpcs:ignore
                    $result['scripts'] = array_diff_key($result['scripts'], array_flip($scriptsToRemove));
                }
            }
        }

        return $result;
    }

    /**
     * Get cache filename.
     *
     * @return string
     */
    public function getname($flag = false)
    {
        if ($flag) {
            return $this->filename;
        }
        $date = time();

        return $this->filename . '?date=' . $date;
    }

    protected static function is_valid_cache_file($dir, $file)
    {
        //check if is valid file

        return '.' !== $file && '..' !== $file && false !== strpos($file, TWO_CACHEFILE_PREFIX)
            && is_file($dir . $file);
    }

    /**
     * Clears contents of TWO_CACHE_DIR.
     *
     * @return void
     */
    protected static function clear_cache_classic()
    {
        $contents = self::get_cache_contents();

        foreach ($contents as $name => $files) {
            $dir = rtrim(TWO_CACHE_DIR . $name, '/') . '/';

            foreach ($files as $file) {
                if (self::is_valid_cache_file($dir, $file)) {
                    @unlink($dir . $file); // @codingStandardsIgnoreLine
                }
            }
        }
        @unlink(TWO_CACHE_DIR . '/.htaccess'); // @codingStandardsIgnoreLine
    }

    /**
     * Recursively deletes the specified pathname (file/directory) if possible.
     * Returns true on success, false otherwise.
     *
     * @param string $pathname pathname to remove
     *
     * @return bool
     */
    protected static function rmdir($pathname)
    {
        $files = self::get_dir_contents($pathname);

        foreach ($files as $file) {
            $path = $pathname . '/' . $file;

            if (is_dir($path)) {
                self::rmdir($path);
            } else {
                unlink($path); // phpcs:ignore
            }
        }

        return rmdir($pathname); // phpcs:ignore
    }

    /**
     * Clears contents of TWO_CACHE_DIR by renaming the current
     * cache directory into a new one with a unique name and then
     * re-creating the default (empty) cache directory.
     *
     * @return bool returns true when everything is done successfully, false otherwise
     */
    protected static function clear_cache_via_rename()
    {
        $ok = false;
        $dir = self::get_pathname_base();
        $new_name = self::get_unique_name();
        // Makes sure the new pathname is on the same level...
        $new_pathname = dirname($dir) . '/' . $new_name;
        $renamed = @rename($dir, $new_pathname); // @codingStandardsIgnoreLine

        return $ok;
    }

    /**
     * Returns a (hopefully) unique new cache folder name for renaming purposes.
     *
     * @return string
     */
    protected static function get_unique_name()
    {
        $prefix = self::get_advanced_cache_clear_prefix();

        return uniqid($prefix, true);
    }

    /**
     * Get cache prefix name used in advanced cache clearing mode.
     *
     * @return string
     */
    protected static function get_advanced_cache_clear_prefix()
    {
        $pathname = self::get_pathname_base();
        $basename = basename($pathname);

        return $basename . '-';
    }

    /**
     * Returns an array of file and directory names found within
     * the given $pathname without '.' and '..' elements.
     *
     * @param string $pathname pathname
     *
     * @return array
     */
    protected static function get_dir_contents($pathname)
    {
        return array_slice(scandir($pathname), 2);
    }

    /**
     * Wipes directories which were created as part of the fast cache clearing
     * routine (which renames the current cache directory into a new one with
     * a custom-prefixed unique name).
     *
     * @return bool
     */
    public static function delete_advanced_cache_clear_artifacts()
    {
        $dir = self::get_pathname_base();
        $prefix = self::get_advanced_cache_clear_prefix();
        $parent = dirname($dir);
        $ok = false;
        // Returns the list of files without '.' and '..' elements.
        $files = self::get_dir_contents($parent);

        if (is_array($files) && !empty($files)) {
            foreach ($files as $file) {
                $path = $parent . '/' . $file;
                $prefixed = (false !== strpos($path, $prefix));

                // Removing only our own (prefixed) directories...
                if (is_dir($path) && $prefixed) {
                    $ok = self::rmdir($path);
                }
            }
        }

        return $ok;
    }

    public static function get_path($getBase = true)
    {
        $pathname = self::get_pathname_base();

        if (is_multisite()) {
            $blog_id = get_current_blog_id();
            $pathname .= $blog_id . '/';
        }

        if ($getBase) {
            return $pathname;
        }

        return $pathname;
    }

    /**
     * Returns the base path of our cache directory.
     *
     * @return string
     */
    protected static function get_pathname_base()
    {
        return WP_CONTENT_DIR . TENWEB_SO_CACHE_CHILD_DIR;
    }

    protected static function get_cache_contents()
    {
        $contents = [];

        foreach (['', 'js', 'css'] as $dir) {
            $contents[$dir] = scandir(TWO_CACHE_DIR . $dir);
        }

        return $contents;
    }

    /**
     * Performs a scan of cache directory contents and returns an array
     * with 3 values: count, size, timestamp.
     * count = total number of found files
     * size = total filesize (in bytes) of found files
     * timestamp = unix timestamp when the scan was last performed/finished.
     *
     * @return array
     */
    protected static function stats_scan()
    {
        $count = 0;
        $size = 0;

        // Scan everything in our cache directories.
        foreach (self::get_cache_contents() as $name => $files) {
            $dir = rtrim(TWO_CACHE_DIR . $name, '/') . '/';

            foreach ($files as $file) {
                if (self::is_valid_cache_file($dir, $file)) {
                    if (TWO_CACHE_NOGZIP && (false !== strpos($file, '.js') || false !== strpos($file, '.css') || false !== strpos($file, '.img') || false !== strpos($file, '.txt'))) {
                        // Web server is gzipping, we count .js|.css|.img|.txt files.
                        $count++;
                    } elseif (!TWO_CACHE_NOGZIP && false !== strpos($file, '.none')) {
                        // We are gzipping ourselves via php, counting only .none files.
                        $count++;
                    }
                    $size += filesize($dir . $file);
                }
            }
        }

        return [$count, $size, time()];
    }

    /**
     * Checks if cache dirs exist and create if not.
     * Returns false if not succesful.
     *
     * @return bool
     */
    public static function check_and_create_dirs()
    {
        if (!defined('TWO_CACHE_DIR')) {
            // We didn't set a cache.
            return false;
        }

        foreach (['', 'js', 'css', 'critical'] as $dir) {
            if (!self::check_cache_dir(TWO_CACHE_DIR . $dir)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Ensures the specified `$dir` exists and is writeable.
     * Returns false if that's not the case.
     *
     * @param string $dir directory to check/create
     *
     * @return bool
     */
    protected static function check_cache_dir($dir)
    {
        // Try creating the dir if it doesn't exist.
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0777, true) && !is_dir($dir)) { // phpcs:ignore
                return false;
            } // @codingStandardsIgnoreLine

            if (!file_exists($dir)) {
                return false;
            }
        }

        // If we still cannot write, bail.
        if (!is_writable($dir)) { // phpcs:ignore
            return false;
        }
        // Create an index.html in there to avoid prying eyes!
        $idx_file = rtrim($dir, '/\\') . '/index.html';

        if (!is_file($idx_file)) {
            @file_put_contents($idx_file, '<html><head><meta name="robots" content="noindex, nofollow"></head><body></body></html>'); // phpcs:ignore
        }

        return true;
    }
}
