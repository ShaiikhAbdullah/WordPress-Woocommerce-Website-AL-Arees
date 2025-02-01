<?php

namespace TenWebOptimizer;

/*
 * Base class other (more-specific) classes inherit from.
 */
if (!defined('ABSPATH')) {
    exit;
}

abstract class OptimizerBase
{
    /**
     * Holds content being processed (html, scripts, styles)
     *
     * @var string
     */
    protected $content = '';

    /**
     * Controls debug logging.
     *
     * @var bool
     */
    public $debug_log = false;

    /** @var string */
    public $cdn_url = '';

    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * Reads the page and collects tags.
     *
     * @param array $options options
     *
     * @return bool
     */
    abstract public function read($options);

    /**
     * Joins and optimizes collected things.
     *
     * @return bool
     */
    abstract public function optimize();

    /**
     * Caches the things.
     *
     * @return void
     */
    abstract public function cache();

    /**
     * Returns the content
     *
     * @return string
     */
    abstract public function getcontent();

    /**
     * Tranfsorms a given URL to a full local filepath if possible.
     * Returns local filepath or false.
     *
     * @param string $url URL to transform
     *
     * @return bool|string
     */
    public function getpath($url)
    {
        if (false !== strpos($url, '%')) {
            $url = urldecode($url);
        }
        $site_host = wp_parse_url(TWO_WP_SITE_URL, PHP_URL_HOST);
        $content_host = wp_parse_url(TWO_WP_ROOT_URL, PHP_URL_HOST);
        // Normalizing attempts...
        $double_slash_position = strpos($url, '//');

        if (0 === $double_slash_position) {
            if (is_ssl()) {
                $url = 'https:' . $url;
            } else {
                $url = 'http:' . $url;
            }
        } elseif ((false === $double_slash_position) && (false === strpos($url, $site_host))) {
            if (TWO_WP_SITE_URL === $site_host) {
                $url = TWO_WP_SITE_URL . $url;
            } else {
                $url = TWO_WP_SITE_URL . OptimizerUtils::path_canonicalize($url);
            }
        }

        if ($site_host !== $content_host) {
            $url = str_replace(TWO_WP_CONTENT_URL, TWO_WP_SITE_URL . TWO_WP_CONTENT_NAME, $url);
        }
        // First check; hostname wp site should be hostname of url!
        $url_host = @parse_url($url, PHP_URL_HOST); // @codingStandardsIgnoreLine

        if ($url_host !== $site_host) {
            $multidomains = [];
            $multidomains_wpml = apply_filters('wpml_setting', [], 'language_domains');

            if (!empty($multidomains_wpml)) {
                $multidomains = array_map([$this, 'get_url_hostname'], $multidomains_wpml);
            }

            if (!empty($this->cdn_url)) {
                $multidomains[] = wp_parse_url($this->cdn_url, PHP_URL_HOST);
            }

            if (!empty($multidomains)) {
                if (in_array($url_host, $multidomains)) {
                    $url = str_replace($url_host, $site_host, $url);
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
        // Try to remove "wp root url" from url while not minding http<>https.
        $tmp_ao_root = preg_replace('/https?:/', '', TWO_WP_ROOT_URL);

        if ($site_host !== $content_host) {
            // As we replaced the content-domain with the site-domain, we should match against that.
            $tmp_ao_root = preg_replace('/https?:/', '', TWO_WP_SITE_URL);
        }
        $tmp_url = preg_replace('/https?:/', '', $url);
        $path = str_replace($tmp_ao_root, '', $tmp_url);

        // If path starts with :// or //, this is not a URL in the WP context and
        // we have to assume we can't aggregate.
        if (preg_match('#^:?//#', $path)) {
            // External script/css (adsense, etc).
            return false;
        }
        // Prepend with WP_ROOT_DIR to have full path to file.
        $path = str_replace('//', '/', WP_ROOT_DIR . $path);

        // Final check: does file exist and is it readable?
        if (file_exists($path) && is_file($path) && is_readable($path)) {
            return $path;
        } else {
            return false;
        }
    }

    /**
     * Returns the hostname part of a given $url if we're able to parse it.
     * If not, it returns the original url (prefixed with http:// scheme in case
     * it was missing).
     * Used as callback for WPML multidomains filter.
     *
     * @param string $url URL
     *
     * @return string
     */
    protected function get_url_hostname($url)
    {
        // Checking that the url starts with something vaguely resembling a protocol.
        if ((0 !== strpos($url, 'http')) && (0 !== strpos($url, '//'))) {
            $url = 'http://' . $url;
        }
        // Grab the hostname.
        $hostname = wp_parse_url($url, PHP_URL_HOST);

        // Fallback when parse_url() fails.
        if (empty($hostname)) {
            $hostname = $url;
        }

        return $hostname;
    }

    /**
     * Hides everything between noptimize-comment tags.
     *
     * @param string $markup markup to process
     *
     * @return string
     */
    protected function hide_noptimize($markup)
    {
        return $this->replace_contents_with_marker_if_exists('NOPTIMIZE', '/<!--\s?noptimize\s?-->/', '#<!--\s?noptimize\s?-->.*?<!--\s?/\s?noptimize\s?-->#is', $markup);
    }

    /**
     * Unhide noptimize-tags.
     *
     * @param string $markup markup to process
     *
     * @return string
     */
    protected function restore_noptimize($markup)
    {
        return $this->restore_marked_content('NOPTIMIZE', $markup);
    }

    /**
     * Hides "iehacks" content.
     *
     * @param string $markup markup to process
     *
     * @return string
     */
    protected function hide_iehacks($markup)
    {
        return $this->replace_contents_with_marker_if_exists('IEHACK', // Marker name...
            '<!--[if', // Invalid regex, will fallback to search using strpos()...
            '#<!--\[if.*?\[endif\]-->#is', // Replacement regex...
            $markup);
    }

    /**
     * Restores "hidden" iehacks content.
     *
     * @param string $markup markup to process
     *
     * @return string
     */
    protected function restore_iehacks($markup)
    {
        return $this->restore_marked_content('IEHACK', $markup);
    }

    /**
     * "Hides" content within HTML comments using a regex-based replacement
     * if HTML comment markers are found.
     * `<!--example-->` becomes `%%COMMENTS%%ZXhhbXBsZQ==%%COMMENTS%%`
     *
     * @param string $markup markup to process
     *
     * @return string
     */
    protected function hide_comments($markup)
    {
        return $this->replace_contents_with_marker_if_exists('COMMENTS', '<!--', '#<!--.*?-->#is', $markup);
    }

    /**
     * Restores original HTML comment markers inside a string whose HTML
     * comments have been "hidden" by using `hide_comments()`.
     *
     * @param string $markup markup to process
     *
     * @return string
     */
    protected function restore_comments($markup)
    {
        return $this->restore_marked_content('COMMENTS', $markup);
    }

    /**
     * Replaces the given URL with the CDN-version of it when CDN replacement
     * is supposed to be done.
     *
     * @param string $url URL to process
     *
     * @return string
     */
    public function url_replace_cdn($url)
    {
        // For 2.3 back-compat in which cdn-ing appeared to be automatically
        // including WP subfolder/subdirectory into account as part of cdn-ing,
        // even though it might've caused serious troubles in certain edge-cases.
        $cdn_url = OptimizerUtils::tweak_cdn_url_if_needed($this->cdn_url);

        // Allows API/filter to further tweak the cdn url...
        if (!empty($cdn_url)) {
            $this->debug_log('before=' . $url);
            // Simple str_replace-based approach fails when $url is protocol-or-host-relative.
            $is_protocol_relative = OptimizerUtils::is_protocol_relative($url);
            $is_host_relative = (!$is_protocol_relative && ('/' === $url[0]));
            $cdn_url = rtrim($cdn_url, '/');

            if ($is_host_relative) {
                // Prepending host-relative urls with the cdn url.
                $url = $cdn_url . $url;
            } else {
                // Either a protocol-relative or "regular" url, replacing it either way.
                if ($is_protocol_relative) {
                    // Massage $site_url so that simple str_replace() still "works" by
                    // searching for the protocol-relative version of TWO_WP_SITE_URL.
                    $site_url = str_replace(['http:', 'https:'], '', TWO_WP_SITE_URL);
                } else {
                    $site_url = TWO_WP_SITE_URL;
                }
                $this->debug_log('`' . $site_url . '` -> `' . $cdn_url . '` in `' . $url . '`');
                $url = str_replace($site_url, $cdn_url, $url);
            }
            $this->debug_log('after=' . $url);
        }

        return $url;
    }

    /**
     * Returns true if given `$tag` is found in the list of `$list_for_delay`.
     *
     * @param string $tag            tag to search for
     * @param array  $list_for_delay list of scripts considered to be delayed
     *
     * @return bool
     */
    protected function isfordelay($tag, $list_for_delay)
    {
        if (false !== strpos($tag, OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE)) {
            return false;
        }

        foreach ($list_for_delay as $match) {
            if (false !== strpos($tag, $match)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if given `$tag` is found in the list of `$removables`.
     *
     * @param string $tag        tag to search for
     * @param array  $removables list of things considered completely removable
     *
     * @return bool
     */
    protected function isremovable($tag, $removables)
    {
        foreach ($removables as $match) {
            if (false !== strpos($tag, $match)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Callback used in `self::inject_minified()`.
     *
     * @param array $matches regex matches
     *
     * @return string
     */
    public function inject_minified_callback($matches)
    {
        static $conf = null;
        $filepath = null;
        $filehash = null;
        $parts = explode('|', $matches[1]);

        if (!empty($parts)) {
            $filepath = isset($parts[0]) ? base64_decode($parts[0]) : null;
            $filehash = isset($parts[1]) ? $parts[1] : null;
        }

        // Bail early if something's not right...
        if (!$filepath || !$filehash) {
            return "\n";
        }
        $filecontent = file_get_contents($filepath); // phpcs:ignore
        // Some things are differently handled for css/js...
        $is_js_file = ('.js' === substr($filepath, -3, 3));
        $is_css_file = false;

        if (!$is_js_file) {
            $is_css_file = ('.css' === substr($filepath, -4, 4));
        }
        // BOMs being nuked here unconditionally (regardless of where they are)!
        $filecontent = preg_replace("#\x{EF}\x{BB}\x{BF}#", '', $filecontent);

        // Remove comments and blank lines.
        if ($is_js_file) {
            $filecontent = preg_replace('#^\s*\/\/.*$#Um', '', $filecontent);
        }
        // Nuke un-important comments.
        $filecontent = preg_replace('#^\s*\/\*[^!].*\*\/\s?#Um', '', $filecontent);
        // Normalize newlines.
        $filecontent = preg_replace('#(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+#', "\n", $filecontent);

        // JS specifics.
        if ($is_js_file) {
            // Append a semicolon at the end of js files if it's missing.
            $last_char = substr($filecontent, -1, 1);

            if (';' !== $last_char && '}' !== $last_char) {
                $filecontent .= ';';
            }
            // Check if try/catch should be used.
            $opt_js_try_catch = 'on';

            if ('on' === $opt_js_try_catch) {
                // It should, wrap in try/catch.
                $filecontent = 'try{' . $filecontent . '}catch(e){}';
            }
        } elseif ($is_css_file) {
            $filecontent = OptimizerStyles::fixurls($filepath, $filecontent);
        } else {
            $filecontent = '';
        }

        // phpcs:ignore Squiz.PHP.CommentedOutCode.Found
        // Return modified (or empty) code/content.
        return "\n" . $filecontent;
    }

    /**
     * Inject already minified code in optimized JS/CSS.
     *
     * @param string $in markup
     *
     * @return string
     */
    protected function inject_minified($in)
    {
        $out = $in;

        if (false !== strpos($in, '%%INJECTLATER%%')) {
            $out = preg_replace_callback('#\/\*\!%%INJECTLATER' . TWO_HASH . '%%(.*?)%%INJECTLATER%%\*\/#is', [
                $this,
                'inject_minified_callback',
            ], $in);
        }

        return $out;
    }

    /**
     * Specialized method to create the INJECTLATER marker.
     * These are somewhat "special", in the sense that they're additionally wrapped
     * within an "exclamation mark style" comment, so that they're not stripped
     * out by minifiers.
     * They also currently contain the hash of the file's contents too (unlike other markers).
     *
     * @param string $filepath filepath
     * @param string $hash     hash
     *
     * @return string
     */
    public static function build_injectlater_marker($filepath, $hash)
    {
        $contents = '/*!' . self::build_marker('INJECTLATER', $filepath, $hash) . '*/';

        return $contents;
    }

    /**
     * Creates and returns a `%%`-style named marker which holds
     * the base64 encoded `$data`.
     * If `$hash` is provided, it's appended to the base64 encoded string
     * using `|` as the separator (in order to support building the
     * somewhat special/different INJECTLATER marker).
     *
     * @param string      $name marker name
     * @param string      $data marker data which will be base64-encoded
     * @param string|null $hash optional
     *
     * @return string
     */
    public static function build_marker($name, $data, $hash = null)
    {
        // Start the marker, add the data.
        $marker = '%%' . $name . TWO_HASH . '%%' . base64_encode($data);

        // Add the hash if provided.
        if (null !== $hash) {
            $marker .= '|' . $hash;
        }
        // Close the marker.
        $marker .= '%%' . $name . '%%';

        return $marker;
    }

    public static function replace_contents_with_marker_if_exists($marker, $search, $re_replace_pattern, $content)
    {
        $found = false;
        $is_regex = OptimizerUtils::str_is_valid_regex($search);

        if ($is_regex) {
            $found = preg_match($search, $content);
        } else {
            $found = (false !== strpos($content, $search));
        }

        if ($found) {
            $content = preg_replace_callback($re_replace_pattern, function ($matches) use ($marker) {
                return OptimizerBase::build_marker($marker, $matches[0]);
            }, $content);
        }

        return $content;
    }

    public static function restore_marked_content($marker, $content)
    {
        if (false !== strpos($content, $marker)) {
            $content = preg_replace_callback('#%%' . $marker . TWO_HASH . '%%(.*?)%%' . $marker . '%%#is', function ($matches) {
                return base64_decode($matches[1]);
            }, $content);
        }

        return $content;
    }

    /**
     * Logs given `$data` for debugging purposes (when debug logging is on).
     *
     * @param mixed $data data to log
     *
     * @return void
     */
    protected function debug_log($data)
    {
        if (!isset($this->debug_log) || !$this->debug_log) {
            return;
        }

        if (!is_string($data) && !is_resource($data)) {
            $data = var_export($data, true); // phpcs:ignore
        }
        error_log($data); // phpcs:ignore
    }

    /**
     * Checks if a single local css/js file can be minified and returns source if so.
     *
     * @param string $filepath filepath
     *
     * @return bool|string to be minified code or false
     */
    protected function prepare_minify_single($filepath)
    {
        // Decide what we're dealing with, return false if we don't know.
        if ($this->str_ends_in($filepath, '.js')) {
            $type = 'js';
        } elseif ($this->str_ends_in($filepath, '.css')) {
            $type = 'css';
        } else {
            return false;
        }
        // Bail if it looks like its already minifed (by having -min or .min
        // in filename) or if it looks like WP jquery.js (which is minified).
        $minified_variants = [
            '-min.' . $type,
            '.min.' . $type,
            'js/jquery/jquery.js',
        ];

        foreach ($minified_variants as $ending) {
            if ($this->str_ends_in($filepath, $ending)) {
                return false;
            }
        }
        // Get file contents, bail if empty.
        $contents = file_get_contents($filepath); // phpcs:ignore

        return $contents;
    }

    protected function build_minify_single_url(OptimizerCache $cache)
    {
        $url = TWO_CACHE_URL . $cache->getname(true);
        $url = $this->url_replace_cdn($url);

        return $url;
    }

    /**
     * Returns true if given $str ends with given $test.
     *
     * @param string $str  string to check
     * @param string $test ending to match
     *
     * @return bool
     */
    protected function str_ends_in($str, $test)
    {
        // @codingStandardsIgnoreStart
        // substr_compare() is bugged on 5.5.11: https://3v4l.org/qGYBH
        // return ( 0 === substr_compare( $str, $test, -strlen( $test ) ) );
        // @codingStandardsIgnoreEnd
        $length = strlen($test);

        return substr($str, -$length, $length) === $test;
    }

    protected function remove_from_html($tag)
    {
        $this->content = str_replace($tag, '', $this->content);
    }
}
