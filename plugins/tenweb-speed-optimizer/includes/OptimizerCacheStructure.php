<?php

namespace TenWebOptimizer;

use DOMDocument;

/**
 * Class OptimizerCacheStructure
 */
class OptimizerCacheStructure
{
    const TWO_CACHE_STRUCTURE_OPTION_PREFIX = 'two_cache_structure_';

    const TWO_SOURCE_NAMES = [
        'src',
        'href',
        'data-twodelayedjs',
        'data-twodelayedcss'
    ];

    /**
     * @var OptimizerCacheStructure
     */
    public static $instance;

    /**
     * @var array
     */
    private $structure;

    /**
     * @var mixed
     */
    private $pageUrlHash;

    /**
     * @var array
     */
    private $tagsToReplace;

    /**
     * @var array
     */
    private $tagsToAdd;

    /**
     * @var array
     */
    private $webFontList;

    /**
     * @var bool
     */
    private $isFromCache = false;

    private $originalContent;

    private $two_files_cache;

    /**
     * @var string
     */
    private $cacheHeaderString = 'BYPASS';

    /**
     * @var object
     */
    private $doc = null;

    /**
     * OptimizerCacheStructure constructor.
     */
    private function __construct()
    {
        //todo check multisite support
        libxml_use_internal_errors(true);
        $this->doc = new DOMDocument();
        $this->pageUrlHash = md5(sanitize_text_field($_SERVER['REQUEST_URI'])); // phpcs:ignore
        $this->structure = get_option(
            self::TWO_CACHE_STRUCTURE_OPTION_PREFIX . $this->pageUrlHash,
            [
                'tagsToReplace' => [],
                'tagsToAdd' => [],
                'webFontList' => [],
            ]
        );

        $this->tagsToReplace = !empty($this->structure['tagsToReplace']) ? $this->structure['tagsToReplace'] : [];
        $this->tagsToAdd = !empty($this->structure['tagsToAdd']) ? $this->structure['tagsToAdd'] : [];
        $this->webFontList = !empty($this->structure['webFontList']) ? $this->structure['webFontList'] : [];
        global $TwoSettings;
        $this->two_files_cache = $TwoSettings->get_settings('two_files_cache');
        global $disableTwoCacheStructureCache;

        if ($disableTwoCacheStructureCache === true) {
            $this->disableCache();
        }
    }

    /**
     * Checks if there is cache structure data in cache
     *
     * @return bool
     */
    public function check($content)
    {
        if ($this->getCacheStatus()) {
            if (is_array($this->tagsToReplace) && is_array($this->tagsToAdd)) {
                foreach ($this->tagsToReplace as $replace_tag) {
                    $replace = $replace_tag['replace'];

                    if (!empty($replace) && $replace != strip_tags($replace)) { // phpcs:ignore
                        if (false === strpos($content, $replace) || !$this->getTagSource($replace)) {
                            return false;
                        }
                    }
                }

                foreach ($this->tagsToAdd as $add_tag) {
                    $replace = $add_tag['tag'];

                    if (!empty($replace) && $replace != strip_tags($replace)) { // phpcs:ignore
                        if (!$this->getTagSource($replace)) {
                            return false;
                        }
                    }
                }
            }

            return !empty($this->tagsToReplace) && !empty($this->tagsToAdd);
        }

        return false;
    }

    /**
     * Makes edits on main content data retrieved from cached
     *
     * @return mixed|string|string[]
     */
    public function retrieve($content)
    {
        $this->originalContent = $content;

        if ($this->check($content)) {
            foreach ($this->tagsToReplace as $tagToReplace) {
                if ($tagToReplace['search'] !== '') {
                    $pos = strpos($content, $tagToReplace['search']);

                    if ($pos !== false) {
                        $content = substr_replace($content, $tagToReplace['replace'], $pos, strlen($tagToReplace['search']));
                    }
                }
            }

            foreach ($this->tagsToAdd as $tagToAdd) {
                $content = OptimizerUtils::inject_in_html($content, $tagToAdd['tag'], $tagToAdd['where']);
            }

            $this->isFromCache = true;
            $this->cacheHeaderString = 'HIT';

            return $content;
        }

        return $this->originalContent;
    }

    /**
     * Updates cache structure in DB
     *
     * @return bool
     */
    public function set()
    {
        global $disableTwoCacheStructureCache;

        if (!empty($this->structure['tagsToAdd']) && !empty($this->structure['tagsToReplace']) && $this->getCacheStatus() && $disableTwoCacheStructureCache !== true) {
            $this->cacheHeaderString = 'MISS';
            $request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field($_SERVER['REQUEST_URI']) : '';
            $timestamp_size = mb_strlen(gmdate('Y-m-d_H:i:s') . ' ' . $request_uri, '8bit');
            $structure_size = mb_strlen(serialize((array) $this->structure), '8bit'); // phpcs:ignore
            $total_size = (int) $timestamp_size + (int) $structure_size;

            if (get_option(self::TWO_CACHE_STRUCTURE_OPTION_PREFIX . 'size')) {
                $total_size = $total_size + (int) get_option(self::TWO_CACHE_STRUCTURE_OPTION_PREFIX . 'size');
            }
            update_option(self::TWO_CACHE_STRUCTURE_OPTION_PREFIX . 'size', $total_size, 'no');
            update_option(self::TWO_CACHE_STRUCTURE_OPTION_PREFIX . 'TIMESTAMP_' . $this->pageUrlHash, gmdate('Y-m-d_H:i:s') . ' ' . $request_uri, 'no'); // phpcs:ignore

            return update_option(self::TWO_CACHE_STRUCTURE_OPTION_PREFIX . $this->pageUrlHash, $this->structure, 'no');
        }

        return false;
    }

    /**
     * init Singleton
     *
     * @return OptimizerCacheStructure
     */
    public static function init()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Whether the content data was populated from cache
     *
     * @return bool
     */
    public function isFromCache()
    {
        if ($this->getCacheStatus()) {
            return $this->isFromCache;
        }

        return false;
    }

    /**
     * Returns webFontList
     *
     * @return array|mixed
     */
    public function getWebFontList()
    {
        return $this->webFontList;
    }

    /**
     * Adds array of tags that are needed to replace
     */
    public function addToTagsToReplace($search, $replace)
    {
        $this->structure['tagsToReplace'][] = [
            'search' => $search,
            'replace' => $replace
        ];
    }

    /**
     * Adds array of what tags and where
     */
    public function addToTagsToAdd($tag, $where)
    {
        $this->structure['tagsToAdd'][] = [
            'tag' => $tag,
            'where' => $where
        ];
    }

    /**
     * Adds font list array to cache structure
     */
    public function addToWebFontList($fonts)
    {
        $this->structure['webFontList'] = $fonts;
    }

    /**
     * Returns Cache Status
     *
     * @return bool
     */
    public function getCacheStatus()
    {
        return $this->two_files_cache === 'on';
    }

    /**
     * Returns Cache Status
     */
    public function disableCache()
    {
        $this->two_files_cache = '';
    }

    /**
     * FLushes cache structure in DB
     */
    public static function flushAllCache()
    {
        global $wpdb;
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '" . self::TWO_CACHE_STRUCTURE_OPTION_PREFIX . "%'"); // phpcs:ignore
    }

    public function addCacheHeaders()
    {
        header('X-TWO-Cache: ' . $this->cacheHeaderString);
    }

    /**
     * Sets cache status
     *
     * @param string $status
     */
    public function setCacheHeaderString($status)
    {
        $this->cacheHeaderString = $status;
    }

    /**
     * Check cache file
     *
     * @param string $data
     *
     * @return bool
     */
    private function getTagSource($data)
    {
        $this->doc->loadHTML($data);
        $libxml_errors = libxml_get_errors();

        if (empty($libxml_errors)) {
            $tags = $this->doc->getElementsByTagName('*');

            foreach (self::TWO_SOURCE_NAMES as $name) {
                foreach ($tags as $tag) {
                    $source = $tag->getAttribute($name);

                    if (!empty($source)) {
                        $url_data = wp_parse_url($source);

                        if (isset($_SERVER['HTTP_HOST']) && isset($url_data['host']) && $_SERVER['HTTP_HOST'] == $url_data['host']) {
                            $file_path = WP_ROOT_DIR . $url_data['path'];

                            if (!file_exists($file_path)) {
                                return false;
                            }
                        }
                    }
                }
            }
        }
        libxml_clear_errors();

        return true;
    }
}
