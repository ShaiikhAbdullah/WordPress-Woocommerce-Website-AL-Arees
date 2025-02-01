<?php

namespace TenWebOptimizer;

use autoptimizeCache;
use Cache_Enabler;
use Endurance_Page_Cache;
use Exception;
use JSMin\JSMin;
use TenWebIO\CompressService;
use WP_Optimize_Cache_Commands;
use WP_Rewrite;
use WP_Term;
use WP_User;
use WpeCommon;

/*
 * General helpers.
 */
if (!defined('ABSPATH')) {
    exit;
}

class OptimizerUtils
{
    const TWO_INCOMPATIBLE_PLUGIN_LIST = [
        'w3-total-cache/w3-total-cache.php',
        'wp-super-cache/wp-cache.php',
        'wp-rocket/wp-rocket.php',
        'rocket-footer-js/rocket-footer-js.php',
        'autoptimize/autoptimize.php',
        'perfmatters/perfmatters.php',
        'wp-fastest-cache/wpFastestCache.php',
        'wp-optimize/wp-optimize.php',
        'wp-asset-clean-up/wpacu.php',
        'rocket-lazy-load/rocket-lazy-load.php',
        'hummingbird-performance/wp-hummingbird.php',
        'wp-hummingbird/wp-hummingbird.php',
        'flying-scripts/flying-scripts.php',
        'async-javascript/async-javascript.php',
        'nitropack/main.php',
        'psn-pagespeed-ninja/pagespeedninja.php',
        'swift-performance-lite/performance.php',
        'swift-performance/performance.php',
        'fast-velocity-minify/fvm.php',
        'wp-performance-score-booster/wp-performance-score-booster.php',
        'ezoic-integration/ezoic-integration.php',
        'a3-lazy-load/a3-lazy-load.php',
        'page-optimize/page-optimize.php',
        'wp-smushit/wp-smush.php',
        'performance-lab/load.php',
        'airlift/airlift.php',
    ];

    const OPTIMIZED_BG_MARKER = '++TWO_OPTIMIZED_BG_IMAGE++';

    const SVG_DATA = 'data:image/svg+xml,%3Csvg%20xmlns=%22http://www.w3.org/2000/svg%22%20viewBox=%220%200%20';

    const BG_SVG_PLACEHOLDER = OptimizerUtils::SVG_DATA . '%20' . '%22%3E%3C/svg%3E' . '#}';

    const BACKGROUND_IMAGE_REGEXP_NEW__PERFECT__SLOW = "~(?:\})*(.*)\{(?:[^}]|\w\s\S)*(?:background[-image]*\s*:.*url.*\(\s*((?:[\'\"]{1}(?:.*)?[\'\"]{1})|(?:[^\'\"]{1}(?:.*)?[^\'\"]{1}))\s*\))+[^}]*\}~U";

    const BACKGROUND_IMAGE_REGEXP_OLD__NOT_PERFECT__FAST = "~(.*)\{(?:[^}]|\w\s\S)*(?:background[-image]*?\s*:.*?url.*?\(\s*((?:[\'|\"]{1}(?:.*?)?[\'|\"]{1})|(?:[^\'|\"]{1}(?:.*?)?[^\'|\"]{1}))\s*\))+[^}]*\}~";

    const FROM_PLUGIN = '10webspeedoptimizer';

    const MODES = [
        'extreme' => [
            'title' => 'Extreme',
            'mode' => 'extreme',
            'two_delay_all_js_execution' => true,
            'critical_enabled' => true,
            'lazy_load_type' => 'vanilla',
            'next' => 'strong',
            'level' => 1,
        ],
        'strong' => [
            'title' => 'Strong',
            'mode' => 'strong',
            'two_delay_all_js_execution' => true,
            'critical_enabled' => false,
            'lazy_load_type' => 'vanilla',
            'next' => 'balanced',
            'level' => 2,
        ],
        'balanced' => [
            'title' => 'Balanced',
            'mode' => 'balanced',
            'two_delay_all_js_execution' => false,
            'critical_enabled' => true,
            'lazy_load_type' => 'vanilla',
            'next' => 'standard',
            'level' => 3,
        ],
        'standard' => [
            'title' => 'Standard',
            'mode' => 'standard',
            'two_delay_all_js_execution' => false,
            'critical_enabled' => false,
            'lazy_load_type' => 'browser',
            'next' => 'no_optimize',
            'level' => 4,
        ],
        'no_optimize' => [
            'title' => 'No optimize',
            'mode' => 'no_optimize',
            'next' => '0',
            'level' => 5,
        ],
    ];

    private static $has_changed_bg_image = false;

    /**
     * Returns true when mbstring is available.
     *
     * @param bool|null $override allows overriding the decision
     *
     * @return bool
     */
    public static function mbstring_available($override = null)
    {
        static $available = null;

        if (null === $available) {
            $available = \extension_loaded('mbstring');
        }

        if (null !== $override) {
            $available = $override;
        }

        return $available;
    }

    /**
     * Multibyte-capable strpos() if support is available on the server.
     * If not, it falls back to using \strpos().
     *
     * @param string      $haystack haystack
     * @param string      $needle   needle
     * @param int         $offset   offset
     * @param string|null $encoding Encoding. Default null.
     *
     * @return int|false
     */
    public static function strpos($haystack, $needle, $offset = 0, $encoding = null)
    {
        if (self::mbstring_available()) {
            return (null === $encoding) ? \mb_strpos($haystack, $needle, $offset) : \mb_strpos($haystack, $needle, $offset, $encoding);
        } else {
            return \strpos($haystack, $needle, $offset);
        }
    }

    /**
     * Finds the position of the <body> tag in an HTML string.
     *
     * This method searches for the first occurrence of the <body> tag
     * in the given HTML content. If a </head> tag is found after the <body>
     * tag, it will search for the next <body> tag.
     *
     * @param string $haystack the HTML content as a string
     *
     * @return int|false the position of the <body> tag, or false if not found
     */
    public static function get_body_position($haystack)
    {
        // Find the first occurrence of the <body> tag
        $bodyPosition = self::strpos($haystack, '<body');

        // If <body> tag is not found, return false
        if ($bodyPosition === false) {
            return false;
        }

        // Extract substring starting from the <body> tag
        $afterBody = substr($haystack, $bodyPosition);

        // Check if there's a </head> tag after the found <body> tag
        $headClosePosition = self::strpos($afterBody, '</head>');

        // If </head> is found after <body>, search for the next <body> tag
        if ($headClosePosition !== false) {
            // Search for the next <body> tag after </head>
            $bodyPosition = self::strpos($haystack, '<body', $bodyPosition + 1);
        }

        return $bodyPosition;
    }

    /**
     * Multibyte-capable strrpos() if support is available on the server.
     * If not, it falls back to using \strrpos().
     *
     * @param string      $haystack haystack
     * @param string      $needle   needle
     * @param int         $offset   offset
     * @param string|null $encoding Encoding. Default null.
     *
     * @return int|false
     */
    public static function strrpos($haystack, $needle, $offset = 0, $encoding = null)
    {
        if (self::mbstring_available()) {
            return (null === $encoding) ? \mb_strrpos($haystack, $needle, $offset) : \mb_strrpos($haystack, $needle, $offset, $encoding);
        } else {
            return \strrpos($haystack, $needle, $offset);
        }
    }

    /**
     * Attempts to return the number of characters in the given $string if
     * mbstring is available. Returns the number of bytes
     * (instead of characters) as fallback.
     *
     * @param string      $string   string
     * @param string|null $encoding encoding
     *
     * @return int number of charcters or bytes in given $string
     *             (characters if/when supported, bytes otherwise)
     */
    public static function strlen($string, $encoding = null)
    {
        if (self::mbstring_available()) {
            return (null === $encoding) ? \mb_strlen($string) : \mb_strlen($string, $encoding);
        } else {
            return \strlen($string);
        }
    }

    /**
     * Our wrapper around implementations of \substr_replace()
     * that attempts to not break things horribly if at all possible.
     * Uses mbstring if available, before falling back to regular
     * substr_replace() (which works just fine in the majority of cases).
     *
     * @param string      $string      string
     * @param string      $replacement replacement
     * @param int         $start       start offset
     * @param int|null    $length      length
     * @param string|null $encoding    encoding
     *
     * @return string
     */
    public static function substr_replace($string, $replacement, $start, $length = null, $encoding = null)
    {
        if (self::mbstring_available()) {
            $strlen = self::strlen($string, $encoding);

            if ($start < 0) {
                if (-$start < $strlen) {
                    $start = $strlen + $start;
                } else {
                    $start = 0;
                }
            } elseif ($start > $strlen) {
                $start = $strlen;
            }

            if (null === $length || '' === $length) {
                $start2 = $strlen;
            } elseif ($length < 0) {
                $start2 = $strlen + $length;

                if ($start2 < $start) {
                    $start2 = $start;
                }
            } else {
                $start2 = $start + $length;
            }

            if (null === $encoding) {
                $leader = $start ? \mb_substr($string, 0, $start) : '';
                $trailer = ($start2 < $strlen) ? \mb_substr($string, $start2, null) : '';
            } else {
                $leader = $start ? \mb_substr($string, 0, $start, $encoding) : '';
                $trailer = ($start2 < $strlen) ? \mb_substr($string, $start2, null, $encoding) : '';
            }

            return "{$leader}{$replacement}{$trailer}";
        }

        return (null === $length) ? \substr_replace($string, $replacement, $start) : \substr_replace($string, $replacement, $start, $length);
    }

    /**
     * Decides whether this is a "subdirectory site" or not.
     *
     * @param bool $override allows overriding the decision when needed
     *
     * @return bool
     */
    public static function siteurl_not_root($override = null)
    {
        static $subdir = null;

        if (null === $subdir) {
            $parts = self::get_ao_wp_site_url_parts();
            $subdir = (isset($parts['path']) && ('/' !== $parts['path']));
        }

        if (null !== $override) {
            $subdir = $override;
        }

        return $subdir;
    }

    /**
     * Parse TWO_WP_SITE_URL into components using \parse_url(), but do
     * so only once per request/lifecycle.
     *
     * @return array
     */
    public static function get_ao_wp_site_url_parts()
    {
        static $parts = [];

        if (empty($parts)) {
            $parts = \wp_parse_url(TWO_WP_SITE_URL);
        }

        return $parts;
    }

    /**
     * Modify given $cdn_url to include the site path when needed.
     *
     * @param string $cdn_url          CDN URL to tweak
     * @param bool   $force_cache_miss force a cache miss in order to be able
     *                                 to re-run the filter
     *
     * @return string
     */
    public static function tweak_cdn_url_if_needed($cdn_url, $force_cache_miss = false)
    {
        static $results = [];

        if (!isset($results[$cdn_url]) || $force_cache_miss) {

            // In order to return unmodified input when there's no need to tweak.
            $results[$cdn_url] = $cdn_url;

            // Behind a default true filter for backcompat, and only for sites
            // in a subfolder/subdirectory, but still easily turned off if
            // not wanted/needed...
            if (OptimizerUtils::siteurl_not_root()) {
                $site_url_parts = OptimizerUtils::get_ao_wp_site_url_parts();
                $cdn_url_parts = \wp_parse_url($cdn_url);
                $schemeless = self::is_protocol_relative($cdn_url);
                $cdn_url_parts = self::maybe_replace_cdn_path($site_url_parts, $cdn_url_parts);

                if (false !== $cdn_url_parts) {
                    $results[$cdn_url] = self::assemble_parsed_url($cdn_url_parts, $schemeless);
                }
            }
        }

        return $results[$cdn_url];
    }

    /**
     * When siteurl contans a path other than '/' and the CDN URL does not have
     * a path or it's path is '/', this will modify the CDN URL's path component
     * to match that of the siteurl.
     * This is to support "magic" CDN urls that worked that way before v2.4...
     *
     * @param array $site_url_parts site URL components array
     * @param array $cdn_url_parts  CDN URL components array
     *
     * @return array|false
     */
    public static function maybe_replace_cdn_path(array $site_url_parts, array $cdn_url_parts)
    {
        if (isset($site_url_parts['path']) && '/' !== $site_url_parts['path']) {
            if (!isset($cdn_url_parts['path']) || '/' === $cdn_url_parts['path']) {
                $cdn_url_parts['path'] = $site_url_parts['path'];

                return $cdn_url_parts;
            }
        }

        return false;
    }

    /**
     * Given an array or components returned from \parse_url(), assembles back
     * the complete URL.
     * If optional
     *
     * @param array $parsed_url URL components array
     * @param bool  $schemeless whether the assembled URL should be
     *                          protocol-relative (schemeless) or not
     *
     * @return string
     */
    public static function assemble_parsed_url(array $parsed_url, $schemeless = false)
    {
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';

        if ($schemeless) {
            $scheme = '//';
        }
        $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass = isset($parsed_url['pass']) ? ':' . $parsed_url['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';

        return "$scheme$user$pass$host$port$path$query$fragment";
    }

    /**
     * Returns true if given $url is protocol-relative.
     *
     * @param string $url URL to check
     *
     * @return bool
     */
    public static function is_protocol_relative($url)
    {
        $result = false;

        if (!empty($url)) {
            $result = (0 === strpos($url, '//'));
        }

        return $result;
    }

    /**
     * Canonicalizes the given path regardless of it existing or not.
     *
     * @param string $path path to normalize
     *
     * @return string
     */
    public static function path_canonicalize($path)
    {
        $patterns = [
            '~/{2,}~',
            '~/(\./)+~',
            '~([^/\.]+/(?R)*\.{2,}/)~',
            '~\.\./~',
        ];
        $replacements = [
            '/',
            '/',
            '',
            '',
        ];

        return preg_replace($patterns, $replacements, $path);
    }

    /**
     * Returns true if the string is a valid regex.
     *
     * @param string $string string, duh
     *
     * @return bool
     */
    public static function str_is_valid_regex($string)
    {
        set_error_handler(function () {}, E_WARNING); // phpcs:ignore
        $is_regex = (false !== preg_match($string, ''));
        restore_error_handler();

        return $is_regex;
    }

    /**
     * Returns true if a certain WP plugin is active/loaded.
     *
     * @param string $plugin_file main plugin file
     *
     * @return bool
     */
    public static function is_plugin_active($plugin_file)
    {
        static $ipa_exists = null;

        if (null === $ipa_exists) {
            if (!function_exists('\is_plugin_active')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            $ipa_exists = function_exists('\is_plugin_active');
        }

        return $ipa_exists && \is_plugin_active($plugin_file);
    }

    public static function replace_font($css)
    {
        global $TwoSettings;
        $two_async_font = $TwoSettings->get_settings('two_async_font');

        if (isset($two_async_font) && $two_async_font == 'on') {
            /*$re = '~(?>@font-face\s*{\s*|\G(?!\A))(\S+)\s*:\s*valet([^;]+);\s*~';*/ // phpcs:ignore
            $re = '/@font-face.*{\K[^}]*(?=})/';
            preg_match_all($re, $css, $matches, PREG_SET_ORDER, 0);

            foreach ($matches as $el) {
                if (isset($el[0])) {
                    if (strpos($el[0], 'font-display') !== false) {
                        $re1 = '/font-display\s*:\s*\K[^;]*(?=;)/';
                        preg_match_all($re1, $el[0], $elMatches, PREG_SET_ORDER, 0);

                        if (isset($elMatches, $elMatches[0], $elMatches[0][0])) {
                            $style = str_replace($elMatches[0][0], 'swap;', $el[0]);
                            $css = str_replace($el[0], $style, $css);
                        }
                    } else {
                        $style = $el[0] . ';font-display: swap;';
                        $css = str_replace($el[0], $style, $css);
                    }
                }
            }
        }

        return $css;
    }

    public static function replace_google_font_url($url)
    {
        if (strpos($url, 'display')) {
            $url = str_replace('&amp;', '&', $url);
            $parsed_url = wp_parse_url(urldecode($url));
            parse_str($parsed_url['query'], $url_params);

            if (isset($url_params['display'])) {
                $url = str_replace($url_params['display'], 'swap', $url);
            } else {
                $url = add_query_arg('display', 'swap', $url);
            }
        } else {
            $url = add_query_arg('display', 'swap', $url);
        }

        return $url;
    }

    public static function serve_different_sizes_for_critical_bg_image($images_data)
    {
        //tenweb_optimizer_mobile
        $imagesArray = [];
        $css = '';

        if (is_array($images_data)) {
            $allSizes = get_intermediate_image_sizes(); // phpcs:ignore

            foreach ($images_data as $image_data) {
                if (isset($image_data[ 'bg_url' ]) && isset($image_data[ 'selector' ]) && is_array($image_data[ 'selector' ])) {
                    $imageId = self::getImageIdByUrl(strtok($image_data[ 'bg_url' ], '?'));

                    if ($imageId) {
                        $css_rule = implode(':not(.two_bg), ', $image_data['selector']);
                        $css_rule .= ':not(.two_bg)';

                        //create an array with all image sizes
                        foreach ($allSizes as $i => $size) {
                            $imagesArray[ $size ] = wp_get_attachment_image_src($imageId, $size);
                        }
                        $important = '';

                        if (isset($image_data[ 'value' ]) && strpos($image_data[ 'value' ], '!important') < 1) {
                            $important = ' !important; ';
                        }
                        $mobileRule = '';

                        foreach ($imagesArray as $size => $imageArray) {
                            if (!empty($imageArray)) {
                                if ($size === 'medium_large' && empty($mobileRule)) {
                                    if (isset($imageArray[ 0 ]) && !empty($imageArray[ 0 ])) {
                                        if (isset($image_data[ 'value' ]) && strpos($image_data[ 'value' ], $image_data[ 'bg_url' ]) !== false) {
                                            $css_param_value = str_replace($image_data[ 'bg_url' ], $imageArray[ 0 ], $image_data[ 'value' ]);
                                            $mobileRule = ' background-image: ' . $css_param_value . $important;
                                        } else {
                                            $mobileRule = ' background-image: url(' . $imageArray[ 0 ] . ') !important; ';
                                        }
                                    }
                                    break;
                                }
                            }
                        }

                        //fallback to elementor images
                        if (empty($mobileRule)) {
                            if (isset($imagesArray[ 'tenweb_optimizer_mobile' ])) {
                                if (isset($imagesArray[ 'tenweb_optimizer_mobile' ][ 0 ]) && !empty($imagesArray[ 'tenweb_optimizer_mobile' ][ 0 ])) {
                                    if (isset($image_data[ 'value' ]) && strpos($image_data[ 'value' ], $image_data[ 'bg_url' ]) !== false) {
                                        $css_param_value = str_replace($image_data[ 'bg_url' ], $imagesArray[ 'tenweb_optimizer_mobile' ][ 0 ], $image_data[ 'value' ]);
                                        $mobileRule = ' background-image: ' . $css_param_value . $important;
                                    } else {
                                        $mobileRule = ' background-image: url(' . $imagesArray[ 'tenweb_optimizer_mobile' ][ 0 ] . ') !important; ';
                                    }
                                }
                            }
                        }

                        //generate media css blocks and add to the end of css file
                        if (!empty($mobileRule)) {
                            $mobileCss = "\r\n" . rtrim($css_rule, ',')
                                . ' { ' . $mobileRule . ' } ';
                            $css .= ' ' . $mobileCss;
                        }
                    }
                }
            }
        }

        if ($css) {
            $css = self::replace_bg($css);
            $css = "/* Autogenerated by 10Web Booster plugin*/\r\n
                    @media (min-width: 320px) and (max-width: 480px) { \r\n" . $css . '}';
        }

        return $css;
    }

    public static function getImageIdByUrl($url)
    {
        global $wpdb;
        // If the URL is auto-generated thumbnail, remove the sizes and get the URL of the original image
        $url = preg_replace('/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $url);
        // phpcs:ignore Squiz.PHP.CommentedOutCode.Found
        //    $imgid = attachment_url_to_postid($url); // phpcs:ignore
        $wp_uploads = wp_upload_dir();
        $attachment_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value = %s LIMIT 1", str_replace($wp_uploads['baseurl'] . '/', '', $url))); // phpcs:ignore

        return $attachment_id;
    }

    /**
     * This is the copy of WP function to regenerate metadata if it is set but is missing sizes for some reason.
     * The original is in wp-includes/media.php
     *
     * Maybe attempts to generate attachment metadata, if missing.
     *
     * @param WP_Post $attachment attachment object
     */
    public static function wp_maybe_generate_attachment_metadata($attachment)
    {
        if (empty($attachment) || empty($attachment->ID)) {
            return;
        }

        $attachment_id = (int) $attachment->ID;
        $file = get_attached_file($attachment_id);
        $meta = wp_get_attachment_metadata($attachment_id);

        if (empty($meta) && file_exists($file)) {
            $_meta = get_post_meta($attachment_id);
            $_lock = 'wp_generating_att_' . $attachment_id;

            if ((! array_key_exists('_wp_attachment_metadata', $_meta) || empty($_meta[ '_wp_attachment_metadata' ][ 'sizes' ])) && ! \TenWebWpTransients\OptimizerTransients::get($_lock)) {
                \TenWebWpTransients\OptimizerTransients::set($_lock, $file);
                wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $file));
                \TenWebWpTransients\OptimizerTransients::delete($_lock);
            }
        }
    }

    public static function replace_bg($css)
    {
        /* Exclude ::after and ::before elements from CSS because if we change their bg urls,
        we cannot restore it on the fronted using JS  */
        $css_without_after_before = $css;

        if (preg_match_all('#(::before.*})|(::after.*})#Usmi', $css_without_after_before, $css_matches)) {
            foreach ($css_matches[0] as $css_block) {
                $css_without_after_before = str_replace($css_block, '}', $css_without_after_before);
            }
        }

        $replaced_images = [];
        global $TwoSettings;
        $two_lazyload = $TwoSettings->get_settings('two_lazyload');
        $two_bg_lazyload = $TwoSettings->get_settings('two_bg_lazyload');
        $two_img_in_viewport_lazyload = $TwoSettings->get_settings('two_img_in_viewport_lazyload');
        $critical = new OptimizerCriticalCss();

        if (TWO_LAZYLOAD && isset($two_bg_lazyload) && ($two_bg_lazyload == 'on' || ($two_img_in_viewport_lazyload == 'on' && $critical->images_in_viewport)) && !$critical->use_uncritical) {
            // phpcs:ignore Squiz.PHP.CommentedOutCode.Found
            //$re = '~\bbackground[-image]*?\s*:.*?url.*?\(\s*[\'|"]?(.*?)?[\'|"]?\s*\)~i'; // phpcs:ignore
            //$re = '~url\s*\(\s*[\'|"]?(.*?)?[\'|"]?\s*\)~i'; // phpcs:ignore
            //$re = '~\bbackground[-image]*?\s*:.*?url.*?\(\s*[\'|"]?(.*?)?[\'|"]\s*\)~i'; // phpcs:ignore
            $re = '~\bbackground[-image]*?\s*:.*?url.*?\(\s*?(.*?)?\s*\)~i';
            preg_match_all($re, $css_without_after_before, $matches);
            $ext_list = ['svg', 'jpeg', 'png', 'gif', 'jpg', 'webp', 'bmp'];

            if (isset($matches[1]) && is_array($matches)) {
                $images_urls = $matches[1];
                $bg_styles = $matches[0];

                foreach ($images_urls as $key => $src) {
                    $src = htmlspecialchars_decode($src, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401);
                    $src = str_replace(["'", '"'], [''], $src);
                    $url = strtok($src, '?');
                    $ext = pathinfo($url, PATHINFO_EXTENSION);
                    $current_bg_style = '';

                    if (is_array($bg_styles) && isset($bg_styles[$key])) {
                        $current_bg_style = $bg_styles[$key];
                    }

                    if (!in_array(strtolower($ext), $ext_list)) {
                        continue;
                    }

                    if (in_array($src, $replaced_images)) {
                        continue;
                    }

                    //pass serve_different_sizes_for_bg_image added backgrounds
                    if (self::strpos($src, self::OPTIMIZED_BG_MARKER) !== false) {
                        continue;
                    }
                    $flag_continue = false;
                    $two_exclude_lazyload = $TwoSettings->get_settings('two_exclude_lazyload');

                    if (isset($two_exclude_lazyload) && !empty($two_exclude_lazyload)) {
                        $exclude_lazyload = explode(',', $two_exclude_lazyload);

                        foreach ($exclude_lazyload as $path) {
                            if (strpos($src, $path) !== false) {
                                $flag_continue = true;
                            }
                        }
                    }

                    if ($flag_continue === false && $two_img_in_viewport_lazyload == 'on' && $critical->images_in_viewport) {
                        $abs_src = self::get_absolute_url($src);
                        $flag_continue = in_array($abs_src, $critical->images_in_viewport);
                    }

                    if ($flag_continue) {
                        continue;
                    }
                    $replaced_images[] = $src;

                    $pos = strpos($src, '#}');

                    if ($pos === false && !empty($current_bg_style)) {
                        $new_bg_style = str_replace($src, self::BG_SVG_PLACEHOLDER . $src, $current_bg_style);
                        $css = str_replace($current_bg_style, $new_bg_style, $css);
                    } elseif ($pos === false) {
                        $css = str_replace($src, self::BG_SVG_PLACEHOLDER . $src, $css);
                    }
                    self::$has_changed_bg_image = true;
                }
            }
        }

        return $css;
    }

    /**
     * Cache compare.
     *
     * @param array $args
     */
    public static function cache_compare($args = [])
    {
        $type = $args['type'];
        $post_id = $args['post_id'];
        $new_cache_files = $args['new_cache_files'];
        $old_cache_files = get_post_meta($post_id, 'two_cache_' . $type, true);

        if (!empty($new_cache_files) && empty($old_cache_files)) {
            add_post_meta($post_id, 'two_cache_' . $type, []);
        }

        if (!empty($new_cache_files)) {
            update_post_meta($post_id, 'two_cache_' . $type, $new_cache_files);

            if (!empty($old_cache_files)) {
                $dir_gzip = OptimizerCache::get_path();

                foreach ($old_cache_files as $old_file) {
                    if (!in_array($old_file, $new_cache_files)) {
                        $old_file_name = ($type == 'gzip') ? $old_file : $type . '/' . $old_file;
                        self::delete_cache_file($old_file_name);

                        if ($type == 'gzip') {
                            foreach (['deflate', 'none', 'gzip'] as $val) {
                                $file_gzip = $old_file . '.' . $val;

                                if (is_file($dir_gzip . $file_gzip)) {
                                    self::delete_cache_file($file_gzip);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Cache files parsing array.
     *
     * @return array
     */
    public static function cache_files_parsing_array()
    {
        $js = [];
        $css = [];
        $gzip = [];
        $file_name = '_all_cache_files.txt';

        if (is_file(TWO_CACHE_DIR . $file_name)) {
            $files = file_get_contents(TWO_CACHE_DIR . $file_name); // phpcs:ignore

            if (!empty($files)) {
                OptimizerUtils::delete_cache_file($file_name);
                $files = json_decode($files);

                foreach ($files as $file) {
                    preg_match('/^css\/(.*).css$/', $file, $matches_css);
                    preg_match('/^js\/(.*).js$/', $file, $matches_js);
                    preg_match('/^(.*).php$/', $file, $matches_php);

                    if (!empty($matches_css)) {
                        $css[] = str_replace('css/', '', $matches_css[0]);
                    }

                    if (!empty($matches_js)) {
                        if (strpos($matches_js[0], 'two_snippet_') < -1) {
                            $js[] = str_replace('js/', '', $matches_js[0]);
                        }
                    }

                    if (!empty($matches_php)) {
                        if (strpos($matches_php[0], 'two_snippet_') < -1) {
                            $gzip[] = $matches_php[0];
                        }
                    }
                }
            }
        }

        return ['js' => array_unique($js), 'css' => array_unique($css), 'gzip' => array_unique($gzip)];
    }

    /**
     * Delete all cache on DB.
     *
     * @param array $args
     *
     * @return bool
     */
    public static function delete_all_cache_db($args = [])
    {
        global $wpdb;
        $tbl = $wpdb->prefix . 'postmeta';
        $css = $wpdb->delete($tbl, ['meta_key' => 'two_cache_css']); // phpcs:ignore
        $js = $wpdb->delete($tbl, ['meta_key' => 'two_cache_js']); // phpcs:ignore
        $gzip = $wpdb->delete($tbl, ['meta_key' => 'two_cache_gzip']); // phpcs:ignore

        return true;
    }

    /**
     * Deleted recursively directory and its entire contents.
     *
     * @param string $dir
     * @param array  $not_allow_delete
     *
     * @return mixed
     */
    public static function delete_all_cache_file($dir = '', $not_allow_delete = [], $not_allow_folder = null)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);

            if (!is_array($objects)) {
                return false;
            }

            foreach ($objects as $key => $object) {
                if ($object === $not_allow_folder) {
                    continue;
                }

                if ($object != '.' && $object != '..') {
                    if (is_dir($dir . '/' . $object) && !is_link($dir . '/' . $object)) {
                        self::delete_all_cache_file(rtrim($dir, '/') . '/' . $object, $not_allow_delete);
                    } else {
                        unlink($dir . '/' . $object); // phpcs:ignore
                    }
                    unset($objects[$key]);
                }
            }

            if (count($objects) === 2 && (empty($not_allow_delete) || !in_array($dir, $not_allow_delete) || empty($not_allow_folder))) {
                rmdir($dir); // phpcs:ignore
            }

            return true;
        }

        return true;
    }

    public static function delete_files_by_prefix($prefix)
    {
        $dir = OptimizerCache::get_path();
        $mask = $dir . $prefix;
        array_map('unlink', glob($mask));
    }

    /**
     * Delete cache file.
     *
     * @param string $file
     *
     * @return bool
     */
    public static function delete_cache_file($file = '')
    {
        $file = OptimizerCache::get_path() . $file;

        if (is_file($file)) {
            $delete = @unlink($file); // phpcs:ignore

            if ($delete) {
                return true;
            }
        }

        return false;
    }

    public static function get_page_url()
    {
        if (isset($_SERVER['HTTP_HOST']) && isset($_SERVER['REQUEST_URI'])) {
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                $link = 'https';
            } else {
                $link = 'http';
            }
            // Here append the common URL characters.
            $link .= '://';
            // Append the host(domain name, ip) to the URL.
            $link .= sanitize_text_field($_SERVER['HTTP_HOST']);
            // Append the requested resource location to the URL
            $link .= sanitize_text_field($_SERVER['REQUEST_URI']);

            return $link;
        }

        return '';
    }

    public static function is_pagespeed_enabled()
    {
        return defined('TW_NGX_PAGESPEED') && TW_NGX_PAGESPEED === 'enabled';
    }

    public static function is_pagespeed_lazyload_enabled()
    {
        return self::is_pagespeed_enabled() && defined('TW_NGX_PAGESPEED_FILTERS') && in_array('lazyload_images', TW_NGX_PAGESPEED_FILTERS, true);
    }

    public static function is_pagespeed_image_optimization_enables()
    {
        return self::is_pagespeed_enabled() && defined('TW_NGX_PAGESPEED_FILTERS') &&
            (
                in_array('convert_gif_to_png', TW_NGX_PAGESPEED_FILTERS, true) ||
                in_array('recompress_png', TW_NGX_PAGESPEED_FILTERS, true) ||
                in_array('convert_png_to_jpeg', TW_NGX_PAGESPEED_FILTERS, true) ||
                in_array('convert_jpeg_to_progressive', TW_NGX_PAGESPEED_FILTERS, true) ||
                in_array('recompress_jpeg', TW_NGX_PAGESPEED_FILTERS, true) ||
                in_array('convert_jpeg_to_webp', TW_NGX_PAGESPEED_FILTERS, true) ||
                in_array('convert_to_webp_lossless', TW_NGX_PAGESPEED_FILTERS, true) ||
                in_array('convert_to_webp_animated', TW_NGX_PAGESPEED_FILTERS, true) ||
                in_array('recompress_webp', TW_NGX_PAGESPEED_FILTERS, true) ||
                in_array('inline_images', TW_NGX_PAGESPEED_FILTERS, true) ||
                in_array('resize_images', TW_NGX_PAGESPEED_FILTERS, true)
            );
    }

    public static function is_pagespeed_js_defer_enabled()
    {
        return self::is_pagespeed_enabled() && defined('TW_NGX_PAGESPEED_FILTERS') && in_array('defer_javascript', TW_NGX_PAGESPEED_FILTERS, true);
    }

    public static function purge_pagespeed_cache()
    {
        if (OptimizerUtils::is_pagespeed_enabled()) {
            $url = rtrim(get_home_url(), '/') . '/*';
            $response = wp_remote_request($url, ['method' => 'PURGE']);
        }

        return true;
    }

    /**
     * remove markers that serve_different_sizes_for_bg_image added for backgrounds
     *
     * @return string|string[]
     */
    public static function removeBgImageMarkers($css)
    {
        return str_replace(
            self::OPTIMIZED_BG_MARKER,
            '',
            str_replace(
                self::OPTIMIZED_BG_MARKER . self::SVG_DATA . '#}',
                '',
                $css
            )
        );
    }

    /**
     * Run a match on the array's keys
     *
     * @param int $flags
     *
     * @return array
     */
    public static function preg_grep_keys($pattern, $input, $flags = 0)
    {
        return array_intersect_key($input, array_flip(preg_grep($pattern, array_keys($input), $flags)));
    }

    /**
     * Checks if the current request is a WP REST API request.
     *
     * Case #1: After WP_REST_Request initialisation
     * Case #2: Support "plain" permalink settings
     * Case #3: It can happen that WP_Rewrite is not yet initialized,
     *          so do this (wp-settings.php)
     * Case #4: URL Path begins with wp-json/ (your REST prefix)
     *          Also supports WP installations in subfolders
     *
     * @returns boolean
     */
    public static function is_rest()
    {
        if ((defined('REST_REQUEST') && REST_REQUEST)
            || (isset($_GET['rest_route']))) { // phpcs:ignore
            return true;
        }
        global $wp_rewrite;

        if ($wp_rewrite === null) {
            // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
            $wp_rewrite = new WP_Rewrite();
        }

        $rest_url = wp_parse_url(trailingslashit(rest_url()));
        $current_url = wp_parse_url(add_query_arg([]));

        return isset($current_url['path']) && strpos($current_url['path'], $rest_url['path'], 0) === 0;
    }

    /**
     * Get parameters from a URL string
     *
     * @return bool|mixed
     */
    public static function get_url_query($url, $name)
    {
        $url_params = wp_parse_url($url);

        if (is_array($url_params) && isset($url_params['query'])) {
            parse_str($url_params['query'], $query_array);

            if (is_array($query_array) && isset($query_array[$name])) {
                $url_param = $query_array[$name];

                return $url_param;
            }
        }

        return false;
    }

    /**
     * Remove domain part of a url
     *
     * @return string
     */
    public static function remove_domain_part($url)
    {
        $urlparts = wp_parse_url($url);
        $extracted = '';

        if (isset($urlparts['path'])) {
            $extracted = $urlparts['path'];
        }

        if (isset($urlparts['query'])) {
            $extracted .= '?' . $urlparts['query'];
        }

        return $extracted;
    }

    public static function get_javascipt_type($tag)
    {
        preg_match('/type="(.+?)"/', $tag, $matches);

        return isset($matches[1]) ? $matches[1] : 'text/javascript';
    }

    /**
     * Injects/replaces the given payload markup into `$this->content`
     * at the specified location.
     * If the specified tag cannot be found, the payload is appended into
     * $this->content along with a warning wrapped inside <!--noptimize--> tags.
     *
     * @param string $payload markup to inject
     * @param array  $where   Array specifying the tag name and method of injection.
     *                        Index 0 is the tag name (i.e., `</body>`).
     *                        Index 1 specifies ˛'before', 'after' or 'replace'. Defaults to 'before'.
     *
     * @return string
     */
    public static function inject_in_html($content, $payload, $where)
    {

        if ($where[0] === '</body>') {
            $position_function = 'strrpos'; //choose the latest matching element
        } else {
            $position_function = 'strpos'; //choose the first matching element
        }

        if ($where[0] === '<body') {
            $position = self::get_body_position($content);
        } else {
            $position = self::$position_function($content, $where[0]);
        }

        if (false !== $position) {
            // Found the tag, setup content/injection as specified.
            if ('after' === $where[1]) {
                $replacement = $where[0] . $payload;
            } elseif ('replace' === $where[1]) {
                $replacement = $payload;
            } elseif ('after_tag' === $where[1]) {
                $afterTag = mb_substr($content, $position);
                $tagEndPosition = self::$position_function($afterTag, '>');
                $position = $position + $tagEndPosition;
                $replacement = '>' . $payload;
            } else {
                $replacement = $payload . $where[0];
            }
            $replacementLength = strlen($where[0]);

            if ('after_tag' === $where[1]) {
                //count length of '>'
                $replacementLength = 1;
            }
            // Place where specified.
            $content = self::substr_replace($content, $replacement, $position, // Using plain strlen() should be safe here for now, since
                // we're not searching for multibyte chars here still...
                $replacementLength);
        } else {
            // Couldn't find what was specified, just append and add a warning.
            $content .= $payload;
        }

        return $content;
    }

    public static function isJson($string)
    {
        return is_string($string) && (is_object(json_decode($string)) || is_array(json_decode($string)));
    }

    public static function findArr($arr, $field, $value)
    {
        foreach ($arr as $key => $inner_arr) {
            if ($inner_arr[$field] === $value) {
                return $arr[$key];
            }
        }

        return false;
    }

    public static function get_worker_script()
    {
        global $TwoSettings;
        $critical = new OptimizerCriticalCss();
        $merge_gf = '';

        if ($TwoSettings->get_settings('two_merge_google_font_faces') === 'on') {
            $merge_gf = '<script ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ' . OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE . ' type="text/javascript">
                     ' . trim(JSMin::minify(file_get_contents(TENWEB_SO_PLUGIN_DIR . 'includes/external/js/two_merge_google_font_faces.js'))) . '
        </script>';
        }
        $two_font_actions = $TwoSettings->get_settings('two_font_actions');

        return '
        <script ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ' . OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE . ' type="text/javascript">

        </script>' . $merge_gf . '
         <script ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ' . OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE . ' id="two_worker" type="javascript/worker">
                let two_font_actions = "' . $two_font_actions . '";
            ' . trim(JSMin::minify(file_get_contents(TENWEB_SO_PLUGIN_DIR . 'includes/external/js/two_worker.js'))) . '
        </script>
        <script ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ' . OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE . ' type="text/javascript">
                        let two_font_actions = "' . $two_font_actions . '";
                     ' . trim(JSMin::minify(file_get_contents(TENWEB_SO_PLUGIN_DIR . 'includes/external/js/two_delay.js'))) . '
        </script>';
    }

    public static function clear_iframe_src($content)
    {
        if (preg_match_all('#<iframe[^>]*src[^>]*>#Usmi', $content, $matches)) {
            // only used is image optimization is NOT active but lazyload is.
            foreach ($matches[0] as $tag) {
                $new_tag = str_replace(' src=', ' src="" data-two_src=', $tag);
                $content = str_replace($tag, $new_tag, $content);
            }
        }
        $tags_to_remove = [
            [
                'tag' => 'link',
                'attribute' => 'media',
                'value' => 'print',
            ],
            [
                'tag' => 'style',
                'attribute' => 'media',
                'value' => 'print',
            ],
            [
                'tag' => 'script',
                'attribute' => 'src',
                'value' => 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js',
            ],
        ];
        $regex_to_remove_tags = [];

        foreach ($tags_to_remove as $tag) {
            $regex_to_remove_tags[] = '(<' . $tag[ 'tag' ] . '[^>]*' . $tag[ 'attribute' ] . '=[\'"]' . $tag[ 'value' ] . '[\'"].*\/(' . $tag[ 'tag' ] . ')?>)';
        }

        if (preg_match_all('#' . implode('|', $regex_to_remove_tags) . '#Usmi', $content, $matches)) {
            foreach ($matches[0] as $tag) {
                $content = str_replace($tag, '', $content);
            }
        }
        $content = OptimizerUtils::inject_in_html($content, '<script ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ' . esc_attr(OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE) . ' src="' . plugins_url('external/js/two_elementor_video_to_iframe.js', __FILE__) . '"></script>', ['</body>', 'before']);
        /*this code to remove all iframes */
        $content = OptimizerUtils::inject_in_html($content, '<script ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ' . esc_attr(OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE) . '>
                                                                                                           const two_frames = window.frames;
                                                                                                            for (let i = 0; i < two_frames.length; i++) {
                                                                                                                two_frames[i].stop();
                                                                                                            }
                                                                                                           let clear_iframe_interval = setInterval(function(){
                                                                                                                   const two_dom_frames = window.frames;
                                                                                                                    for (let i = 0; i < two_dom_frames.length; i++) {
                                                                                                                        two_dom_frames[i].stop();
                                                                                                                    }
                                                                                                           },20);
                                                                                                           setTimeout(function(){
                                                                                                                  clearInterval(clear_iframe_interval);
                                                                                                           },2000);

                                                                        </script>', ['</body>', 'before']);

        return $content;
    }

    public static function split_css_to_arr($code)
    {
        $return_data = [];
        $return_data['font'] = $code;
        preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $code, $match);

        if (isset($match[0]) && !empty($match[0])) {
            $return_data['urls'] = $match[0];
        }

        return $return_data;
    }

    public static function get_default_critical_pages($status = false)
    {
        // This theme works better without critical. There is a CLS with uncritical loaded.
        $theme = wp_get_theme();

        if ('Twenty Twenty-Two' == $theme->name) {
            $criticalPages = [];
        } else {
            global $TwoSettings;

            $waitUntil = 'load';
            $loadType = 'load_type';
            $use_uncritical = 'off';
            $two_critical_default_settings = get_option('two_critical_default_settings');

            if (isset($two_critical_default_settings['wait_until'])) {
                $waitUntil = $two_critical_default_settings['wait_until'];
            }

            if (isset($two_critical_default_settings['load_type'])) {
                $loadType = $two_critical_default_settings['load_type'];
            }

            if (isset($two_critical_default_settings['use_uncritical'])) {
                $use_uncritical = $two_critical_default_settings['use_uncritical'];
            }
            $page_sizes = OptimizerUtils::get_critical_default_sizes($two_critical_default_settings);

            $homeUrl = get_home_url();
            $pageId = 'front_page';
            $criticalPages = [
                'front_page' => [
                    'title' => 'Home',
                    'url' => $homeUrl,
                    'id' => $pageId,
                    'sizes' => $page_sizes,
                    'load_type' => $loadType,
                    'wait_until' => $waitUntil,
                    'use_uncritical' => $use_uncritical
                ]
            ];

            if ($status) {
                $criticalPages['front_page']['status'] = 'in_progress';
            }
        }

        return $criticalPages;
    }

    /**
     * @param      $regeneration_mode string 'front_page' will generate for front page only
     * @param bool $rightAfterConnect
     *
     * @return void
     */
    public static function regenerate_critical($regeneration_mode = 'front_page', $rightAfterConnect = false)
    {
        global $TwoSettings;
        //todo new_flow_process get and send flow_id

        $two_critical_pages = OptimizerUtils::getCriticalPages();
        $url_query = $TwoSettings->get_settings('two_critical_url_args');

        if (empty($two_critical_pages)) {
            $homeUrl = get_home_url();
            $pageId = 'front_page';

            $waitUntil = 'load';
            $two_critical_default_settings = get_option('two_critical_default_settings');

            if (isset($two_critical_default_settings['wait_until'])) {
                $waitUntil = $two_critical_default_settings['wait_until'];
            }

            $page_sizes = OptimizerUtils::get_critical_default_sizes($two_critical_default_settings);

            $data = [
                'action' => 'two_critical',
                'data' => [
                    'page_url' => $homeUrl,
                    'page_id' => $pageId,
                    'page_sizes' => $page_sizes,
                    'wait_until' => $waitUntil,
                    'url_query' => $TwoSettings->get_settings('two_critical_url_args'),
                    'task' => 'generate',
                    'newly_connected_website' => $rightAfterConnect,
                ],
                'two_critical_sizes' => $TwoSettings->get_settings('two_critical_sizes'),
                'two_critical_pages' => self::get_default_critical_pages(true),
            ];

            if ($rightAfterConnect) {
                $data['data']['flow_id'] = get_site_option(TENWEB_PREFIX . '_flow_id');
            }
            OptimizerCriticalCss::generateCriticalCSS($data);

            if (OptimizerUtils::is_wpml_active()) {
                OptimizerUtils::generate_wpml_home_pages_critical_css($data);
            }
        } else {
            $regenerate_data = \TenWebWpTransients\OptimizerTransients::get('two_regenerate_critical_data');

            if (is_array($regenerate_data) && !empty($regenerate_data)) {
                $two_critical_pages = $regenerate_data;
            } else {
                if ('front_page' == $regeneration_mode) {
                    // Invalidate all critical css.
                    self::update_critical_statuses($two_critical_pages, 'not_started');

                    // Regenerate front page only.
                    foreach ($two_critical_pages as $key => $two_page) {
                        if ('front_page' != $key) {
                            unset($two_critical_pages[$key]);
                        }
                    }
                }
                self::update_critical_statuses($two_critical_pages, 'in_progress');
            }
            $two_critical_sizes = $TwoSettings->get_settings('two_critical_sizes');

            if (!empty($two_critical_pages)) {
                $two_page = reset($two_critical_pages);
                $key = key($two_critical_pages);
                unset($two_critical_pages[$key]);
                $critical_sizes = [];

                if (isset($two_page['sizes']) && is_array($two_page['sizes'])) {
                    foreach ($two_page['sizes'] as $size_id) {
                        if (isset($size_id['uid'], $two_critical_sizes[$size_id['uid']]) && is_array($size_id)) {
                            $critical_sizes[] = $two_critical_sizes[$size_id['uid']];
                        }
                    }
                }
                $data = [
                    'data' => [
                        'page_url' => $two_page['url'],
                        'page_id' => $two_page['id'],
                        'page_sizes' => $critical_sizes,
                        'wait_until' => $two_page['wait_until'],
                        'url_query' => $url_query,
                        'task' => 'generate',
                        'newly_connected_website' => $rightAfterConnect,
                    ],
                ];

                if (isset($two_page['use_uncritical'])) {
                    $data['data']['use_uncritical'] = $two_page['use_uncritical'];
                }
                OptimizerCriticalCss::generateCriticalCSS($data);
                $two_critical_pages_count = count($two_critical_pages);
                \TenWebWpTransients\OptimizerTransients::set('two_regenerate_critical_data', $two_critical_pages, 60 * $two_critical_pages_count);
            }
        }
    }

    public static function get_critical_default_sizes($critical_defaults)
    {
        global $TwoSettings;
        $new_sizes = [];
        $two_critical_sizes = $TwoSettings->get_settings('two_critical_sizes');

        if (is_array($critical_defaults) && isset($critical_defaults['default_sizes'])) {
            foreach ($critical_defaults['default_sizes'] as $key) {
                if (isset($two_critical_sizes[$key])) {
                    $new_sizes[$key] = $two_critical_sizes[$key];
                }
            }
        }

        if (!empty($new_sizes)) {
            return array_keys($new_sizes);
        }

        return array_keys($two_critical_sizes);
    }

    public static function update_critical_statuses($two_critical_pages, $status)
    {
        global $TwoSettings;

        foreach ($two_critical_pages as $key => $two_page) {
            $two_critical_pages[$key]['status'] = $status;
        }
        $TwoSettings->update_setting('two_critical_pages', $two_critical_pages);
    }

    public static function init_defines()
    {
        global $TwoSettings;

        if (!defined('TWO_LAZYLOAD')) {
            define('TWO_LAZYLOAD', true);
        }

        if (!defined('TWO_WP_SITE_URL')) {
            if (function_exists('domain_mapping_siteurl')) {
                define('TWO_WP_SITE_URL', domain_mapping_siteurl(get_current_blog_id()));
            } else {
                define('TWO_WP_SITE_URL', site_url());
            }
        }

        if (!defined('TWO_WP_CONTENT_URL')) {
            if (function_exists('get_original_url')) {
                define('TWO_WP_CONTENT_URL', str_replace(get_original_url(TWO_WP_SITE_URL), TWO_WP_SITE_URL, content_url()));
            } else {
                define('TWO_WP_CONTENT_URL', content_url());
            }
        }

        if (!defined('TWO_WP_CONTENT_NAME')) {
            define('TWO_WP_CONTENT_NAME', '/' . wp_basename(WP_CONTENT_DIR));
        }

        if (!defined('TWO_WP_ROOT_URL')) {
            define('TWO_WP_ROOT_URL', str_replace(TWO_WP_CONTENT_NAME, '', TWO_WP_CONTENT_URL));
        }

        if (!defined('TWO_CACHE_URL')) {
            if (is_multisite()) {
                $blog_id = get_current_blog_id();
                define('TWO_CACHE_URL', TWO_WP_CONTENT_URL . TENWEB_SO_CACHE_CHILD_DIR . $blog_id . '/');
            } else {
                define('TWO_CACHE_URL', TWO_WP_CONTENT_URL . TENWEB_SO_CACHE_CHILD_DIR);
            }
        }

        if (!defined('WP_ROOT_DIR')) {
            define('WP_ROOT_DIR', substr(WP_CONTENT_DIR, 0, strlen(WP_CONTENT_DIR) - strlen(TWO_WP_CONTENT_NAME)));
        }

        if (!defined('TWO_HASH')) {
            define('TWO_HASH', wp_hash(TWO_CACHE_URL));
        }

        if (!defined('TWO_CACHE_NOGZIP')) {
            $two_gzip = $TwoSettings->get_settings('two_gzip');

            if (!TENWEB_SO_HOSTED_ON_10WEB && $two_gzip === 'on') {
                define('TWO_CACHE_NOGZIP', false);
            } else {
                define('TWO_CACHE_NOGZIP', true);
            }
        }

        if (!defined('TWO_CACHEFILE_PREFIX')) {
            define('TWO_CACHEFILE_PREFIX', 'two_');
        }
    }

    public static function flushCloudflareCache($postId = null)
    {
        if (class_exists('\CF\WordPress\Hooks')) {
            $cloudflareHooks = new \CF\WordPress\Hooks();

            if (is_int($postId)) {
                $cloudflareHooks->purgeCacheByRelevantURLs($postId);
            } else {
                $cloudflareHooks->purgeCacheEverything();
            }
        }
    }

    public static function get_tenweb_connection_link($endpoint = 'sign-up', $args = [])
    {
        // copied from manager.py
        $return_url = get_admin_url() . 'admin.php';

        if (is_multisite()) {
            $return_url = network_admin_url() . 'admin.php';
        }

        $token = wp_create_nonce('two_10web_connection');
        update_site_option(TW_OPTIMIZE_PREFIX . '_saved_nonce', $token);
        $return_url_args = ['page' => 'two_settings_page'];
        $register_url_args = [
            'site_url' => urlencode(get_site_url()),
            'utm_source' => '10webspeedoptimizer',
            'from_plugin' => self::FROM_PLUGIN,
            'utm_medium' => 'freeplugin',
            'nonce' => $token,
            'subscr_id' => TENWEB_SO_FREE_SUBSCRIPTION_ID,
            'version' => TENWEB_SO_VERSION,
            'new_connection_flow' => 1,
        ];

        if (!empty($args['old_connection_flow'])) {
            unset($register_url_args['new_connection_flow']);
        }

        if (!empty($args)) {
            $register_url_args = $register_url_args + $args;
            $return_url_args = $return_url_args + $args;
        }

        $register_url_args['return_url'] = urlencode(add_query_arg($return_url_args, $return_url));

        $plugin_from = get_site_option('tenweb_manager_installed');

        if ($plugin_from !== false) {
            $plugin_from = json_decode($plugin_from, true);

            if (is_array($plugin_from) && reset($plugin_from) !== false) {
                $register_url_args['plugin_id'] = reset($plugin_from);

                if (isset($plugin_from['type'])) {
                    $register_url_args['utm_source'] = $plugin_from['type'];
                }
            }
        }

        $url = add_query_arg($register_url_args, TENWEB_DASHBOARD . '/' . $endpoint . '/');

        return $url;
    }

    public static function getCriticalPages()
    {
        global $TwoSettings;

        if (empty($TwoSettings)) {
            return [];
        }
        $two_critical_pages_from_options = $TwoSettings->get_settings('two_critical_pages');
        $two_critical_pages = self::get_meta_values('two_critical_pages');

        if ($two_critical_pages_from_options) {
            $two_critical_pages = array_replace($two_critical_pages_from_options, $two_critical_pages);
        }

        return $two_critical_pages;
    }

    public static function stripslashes_deep($value)
    {
        // copied from wp-includes/formatting.php
        return self::map_deep($value, function ($value) {
            return is_string($value) ? stripslashes($value) : $value;
        });
    }

    public static function map_deep($value, $callback)
    {
        // copied from wp-includes/formatting.php

        if (is_array($value)) {
            foreach ($value as $index => $item) {
                $value[$index] = self::map_deep($item, $callback);
            }
        } elseif (is_object($value)) {
            $object_vars = get_object_vars($value);

            foreach ($object_vars as $property_name => $property_value) {
                $value->$property_name = self::map_deep($property_value, $callback);
            }
        } else {
            $value = call_user_func($callback, $value);
        }

        return $value;
    }

    public static function get_meta_values($key = '')
    {
        if (empty($key)) {
            return null;
        }
        global $wpdb;

        $query = $wpdb->get_results($wpdb->prepare("SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = %s", $key)); // phpcs:ignore
        $result = [];

        foreach ($query as $row) {
            $result[ $row->post_id ] = unserialize($row->meta_value); // phpcs:ignore
        }

        return $result;
    }

    public static function check_if_hosted_website()
    {
        if (is_file(WPMU_PLUGIN_DIR . '/10web-manager/10web-manager.php')) {
            return true;
        }

        return false;
    }

    /* WPML functions*/
    public static function get_wpml_home_urls()
    {
        if (!OptimizerUtils::is_wpml_active()) {
            return [];
        }

        $front_page_id = get_option('page_on_front');

        // if $front_page_id is empty or 0, it means home page is archive page and there is no translation for that page
        if (!$front_page_id) {
            return [];
        }

        $element_id = $front_page_id;
        $element_type = get_post_type($front_page_id);

        $home_pages = [];
        $languages = apply_filters('wpml_active_languages', '');

        foreach ($languages as $lang_code => $language_data) {
            $post_id = apply_filters('wpml_object_id', $element_id, $element_type, false, $lang_code);

            if (!$post_id) {
                continue;
            }
            $home_pages[$lang_code] = [
                'post_id' => $post_id,
                'permalink' => get_permalink($post_id),
                'title' => get_the_title($post_id)
            ];
        }

        return $home_pages;
    }

    public static function add_wpml_home_pages_into_critical_pages($critical_pages = null, $home_url = null)
    {
        /*
         * The function added home pages generated by WPML into critical_pages list, if home page is in that list. The
         * function doesn't generate critical css.
         * */

        global $TwoSettings;

        if ($critical_pages === null) {
            $critical_pages = OptimizerUtils::getCriticalPages();
        }

        if ($home_url === null && $critical_pages['front_page']) {
            $home_url = $critical_pages['front_page']['url'];
        }

        if (!$home_url) {
            return $critical_pages;
        }

        foreach (OptimizerUtils::get_wpml_home_urls() as $lang_code => $post_data) {
            if (rtrim($post_data['permalink'], '/') === rtrim($home_url, '/')) {
                continue;
            }

            if (isset($critical_pages[$post_data['id']])) {
                continue;
            }

            $page_data = $critical_pages['front_page'];
            $page_data['title'] = $post_data['title'];
            $page_data['url'] = $post_data['permalink'];
            $page_data['id'] = $post_data['post_id'];
            $critical_pages[$post_data['post_id']] = $page_data;
        }

        $TwoSettings->update_setting('two_critical_pages', $critical_pages);

        return $critical_pages;
    }

    public static function generate_wpml_home_pages_critical_css($data)
    {
        self::add_wpml_home_pages_into_critical_pages();

        $wpml_data = ['data' => $data['data']];
        $home_page_url = $data['data']['page_url'];

        foreach (self::getCriticalPages() as $page) {
            if ($page['url'] == $home_page_url) {
                continue;
            }

            $is_wpml_page = false;

            foreach (self::get_wpml_home_urls() as $wpml_page) {
                if ($wpml_page['post_id'] == $page['id']) {
                    $is_wpml_page = true;
                    break;
                }
            }

            if ($is_wpml_page === false) {
                continue;
            }

            $wpml_data['data']['page_url'] = $page['url'];
            $wpml_data['data']['page_id'] = $page['id'];
            OptimizerCriticalCss::generateCriticalCSS($wpml_data);
        }
    }

    public static function get_wpml_post_flag_url($post_id)
    {
        if ($post_id == 'front_page') {
            $post_id = get_option('page_on_front');
        }

        if (!$post_id) {
            $lang_code = apply_filters('wpml_default_language', null);
        } else {
            $post_language_details = apply_filters('wpml_post_language_details', null, $post_id);
            $lang_code = $post_language_details['language_code'];
        }

        return plugins_url('sitepress-multilingual-cms/res/flags/' . $lang_code . '.png');
    }

    public static function is_wpml_active()
    {
        return defined('ICL_SITEPRESS_VERSION');
    }

    public static function get_modes($name = null, $preview = false, $level = null)
    {
        $modes = self::MODES;

        if ($preview) {
            foreach ($modes as $key => $mode) {
                $modes[$key]['preview_url'] = add_query_arg(['two_preview' => '1', 'two_level' => $mode['level']], get_home_url() . '/');
            }

            return $modes;
        }

        if (isset($name)) {
            return self::MODES[$name];
        } elseif (isset($level)) {
            foreach ($modes as $key => $mode) {
                if ($mode['level'] === $level) {
                    return $mode;
                }
            }
        }
        $global_mode = get_option('two_default_mode', OptimizerUtils::MODES['extreme']);

        if (is_array($global_mode)) {
            $modes['global'] = $global_mode;
        }

        return $modes;
    }

    public static function testWebPDelivery()
    {
        $requestUrl = TENWEB_SO_URL . '/test/webp_test.jpg';
        $requestArgs = [
            'headers' => [
                'ACCEPT' => 'image/webp'
            ]
        ];
        global $TwoSettings;
        $wpResult = wp_remote_get($requestUrl, $requestArgs); // phpcs:ignore

        if (!is_wp_error($wpResult)) {
            if (isset($wpResult['headers']['content-type']) && 'image/webp' === $wpResult['headers']['content-type']) {
                $TwoSettings->update_setting('two_webp_delivery_working', '1');

                return true;
            }
        }
        $TwoSettings->update_setting('two_webp_delivery_working', '0');

        return false;
    }

    public static function clear_third_party_cache()
    {
        global $wp_fastest_cache, $kinsta_cache, $nginx_purger;

        // if W3 Total Cache is being used, clear the cache
        if (function_exists('w3tc_pgcache_flush')) {
            w3tc_pgcache_flush();
        }

        // if WP Super Cache is being used, clear the cache
        if (function_exists('wp_cache_clean_cache')) {
            global $file_prefix, $supercachedir;

            if (empty($supercachedir) && function_exists('get_supercache_dir')) {
                $supercachedir = get_supercache_dir();
            }
            wp_cache_clean_cache($file_prefix);
        }

        if (class_exists('WpeCommon')) {
            //be extra careful, just in case 3rd party changes things on us
            if (method_exists('WpeCommon', 'purge_memcached')) {
                WpeCommon::purge_memcached();
            }

            if (method_exists('WpeCommon', 'clear_maxcdn_cache')) {
                WpeCommon::clear_maxcdn_cache();
            }

            if (method_exists('WpeCommon', 'purge_varnish_cache')) {
                WpeCommon::purge_varnish_cache();
            }
        }

        if (method_exists('WpFastestCache', 'deleteCache') && !empty($wp_fastest_cache)) {
            $wp_fastest_cache->deleteCache(true);
        }

        if (class_exists('\Kinsta\Cache') && !empty($kinsta_cache) && !empty($kinsta_cache->kinsta_cache_purge)) {
            if (method_exists($kinsta_cache->kinsta_cache_purge, 'purge_complete_caches')) {
                $kinsta_cache->kinsta_cache_purge->purge_complete_caches();
            }
        }

        if (class_exists('\WPaaS\Cache')) {
            if (!\WPaaS\Cache::has_ban()) {
                remove_action('shutdown', [ '\WPaaS\Cache', 'purge' ], PHP_INT_MAX);
                add_action('shutdown', [ '\WPaaS\Cache', 'ban' ], PHP_INT_MAX);
            }
        }

        if (class_exists('WP_Optimize') && defined('WPO_PLUGIN_MAIN_PATH')) {
            if (!class_exists('WP_Optimize_Cache_Commands')) {
                include_once WPO_PLUGIN_MAIN_PATH . 'cache/class-cache-commands.php';
            }

            if (class_exists('WP_Optimize_Cache_Commands')) {
                $wpoptimize_cache_commands = new WP_Optimize_Cache_Commands();
                $wpoptimize_cache_commands->purge_page_cache();
            }
        }

        if (class_exists('Breeze_Admin')) {
            do_action('breeze_clear_all_cache');
        }

        if (defined('LSCWP_V')) {
            do_action('litespeed_purge_all');
        }

        // This function clears the Site Ground cache only if it is on in plugin.
        // As their hosting does not respect plugin's settings sometimes, maybe we'll need to call them directly.
        if (function_exists('sg_cachepress_purge_everything')) {
            sg_cachepress_purge_everything();
        }

        if (class_exists('autoptimizeCache')) {
            autoptimizeCache::clearall();
        }

        if (class_exists('Cache_Enabler')) {
            Cache_Enabler::clear_total_cache();
        }

        if (defined('NGINX_HELPER_BASEPATH') && !empty($nginx_purger)) {
            $nginx_purger->purge_all();
        }

        if (function_exists('rocket_clean_domain')) {
            rocket_clean_domain();
        }

        if (defined('EZOIC_CACHE') && EZOIC_CACHE) {
            $cache = new \Ezoic_Namespace\Ezoic_Integration_Cache();
            $cache->Clear();
        }

        if (class_exists('Endurance_Page_Cache')) {
            $epc = new Endurance_Page_Cache();
            $epc->force_purge = true;
            $epc->purge_all();
        }
    }

    public static function two_flatten(array $array)
    {
        $return = [];
        array_walk_recursive($array, function ($a) use (&$return) { $return[] = $a; });

        return $return;
    }

    public static function triggerPostOptimizationTasks($disabled_incompatible_plugins = [])
    {
        //todo new_flow_process this should be triggered manually when user clicks the apply mode button
        //todo new_flow_process disable test mode, apply mode, call post-optimization
        global $TwoSettings;
        $flow_id = get_site_option(TENWEB_PREFIX . '_flow_id');
        $domain_id = get_site_option('tenweb_domain_id');
        $access_token = get_site_option(TENWEB_PREFIX . '_access_token');
        $two_conflicting_plugins = get_site_option('two_conflicting_plugins');

        if (!is_array($two_conflicting_plugins)) {
            $two_conflicting_plugins = [];
        }

        if ($access_token && $domain_id) {
            $response = wp_remote_post(TENWEB_SO_CRITICAL_URL . '/v1/workspaces/performance/' . $domain_id . '/post-optimization-tasks', [
                'redirection' => 15,
                'blocking' => false,
                'headers' => [
                    'accept' => 'application/x.10webperformance.v1+json',
                    'authorization' => 'Bearer ' . $access_token,
                ],
                'body' => [
                    'notification_id' => sanitize_text_field($_POST['notification_id']), // phpcs:ignore
                    'flow_id' => $flow_id,
                    'disabled_incompatible_plugins' => $disabled_incompatible_plugins,
                    'incompatible_plugins' => $two_conflicting_plugins,
                    'has_excluded_slider' => false
                ],
                'cookies' => []
            ]);

            update_option('two_critical_data_import_response_' . time(), [
                !is_wp_error($response) ? $response['body'] : $response->get_error_message(),
                wp_remote_retrieve_response_code($response)
            ], false);
        }
    }

    public static function check_score()
    {
        $domain_id = get_site_option('tenweb_domain_id');
        $access_token = get_site_option(TENWEB_PREFIX . '_access_token');
        $workspace_id = get_site_option(TENWEB_PREFIX . '_workspace_id');

        if ($access_token && $domain_id && $workspace_id) {
            wp_remote_post(TENWEB_SO_CRITICAL_URL . '/v1/workspaces/' . $workspace_id . '/performance/' . $domain_id . '/googlepagespeed', [
                'redirection' => 15,
                'blocking' => false,
                'headers' => [
                    'accept' => 'application/x.10webperformance.v1+json',
                    'authorization' => 'Bearer ' . $access_token,
                ],
                'body' => [
                ],
                'cookies' => []
            ]);
        }
    }

    public static function set_global_mode()
    {
        global $TwoSettings;
        $modes = self::get_modes();
        $two_critical_status = $TwoSettings->get_settings('two_critical_status');
        $two_delay_all_js_execution = $TwoSettings->get_settings('two_delay_all_js_execution');

        if ($two_critical_status == 'true' && $two_delay_all_js_execution == 'on') {
            $mode = 'extreme';
        } elseif ($two_critical_status == 'true' && $two_delay_all_js_execution != 'on') {
            $mode = 'balanced';
        } elseif ($two_critical_status != 'true' && $two_delay_all_js_execution === 'on') {
            $mode = 'strong';
        } else {
            $mode = 'standard';
        }

        if (isset($modes[$mode])) {
            foreach ($modes[$mode] as $key => $val) {
                if (isset($settings_names[$key])) {
                    $TwoSettings->update_setting($key, $val);
                } elseif ($key === 'critical_enabled') {
                    if ($val) {
                        $TwoSettings->update_setting('two_critical_status', 'true');
                    } else {
                        $TwoSettings->update_setting('two_critical_status', '');
                    }
                }
            }
            update_option('two_default_mode', $modes[$mode]);
        }
    }

    /**
     * @return int|string page_id|front_page|term_$term_id
     */
    public static function get_current_post_info()
    {
        $queried_object = get_queried_object();
        $id = 0;

        if (is_front_page()) {
            $id = 'front_page';
        } elseif (class_exists('WooCommerce') && is_shop()) {
            $id = wc_get_page_id('shop');
        } elseif ($queried_object instanceof WP_Term) {
            $id = 'term_' . $queried_object->term_id;
        } elseif ($queried_object instanceof WP_User) {
            $id = 'user_' . $queried_object->ID;
        } elseif ($queried_object instanceof \WP_Post) {
            $id = $queried_object->ID;
        }

        if ($id === 0) {
            global $wp;
            $home_url = trailingslashit(home_url($wp->request));
            $db_home_url = trailingslashit(get_home_url());

            if ($home_url === $db_home_url) {
                $id = 'front_page';
            }
        }

        return $id;
    }

    public static function get_permalink_name_by_id($id = 'front_page')
    {
        if ('front_page' === $id) {
            return [ 'url' => home_url(), 'title' => 'Home' ];
        } else {
            if (0 === strpos($id, 'term_')) {
                $term_id = (int) ltrim ($id, 'term_');
                $term = get_term($term_id);

                return [ 'url' => get_term_link($term_id), 'title' => $term->name ];
            } elseif (0 === strpos($id, 'user_')) {
                $user_id = (int) ltrim ($id, 'user_');

                return [ 'url' => get_author_posts_url($user_id), 'title' => get_the_author_meta('nicename', $user_id) ];
            } else {
                return [ 'url' => get_permalink($id), 'title' => get_the_title($id) ];
            }
        }
    }

    public static function remove_url_protocol($url)
    {
        $url = rtrim($url, '/');
        $disallowed = ['http://www.', 'https://www.', 'http://', 'https://'];

        foreach ($disallowed as $d) {
            if (strpos($url, $d) === 0) {
                return str_replace($d, '', $url);
            }
        }

        return $url;
    }

    public static function get_post_id($page_url = null)
    {
        $pageUrlHash = md5((string) $page_url); // this function is called in a loop to replace backgrounds, in order not to make a thousand requests to website to find page_id we will cache it in globals
        //phpcs:ignore PHPCompatibility.Variables.ForbiddenGlobalVariableVariable.NonBareVariableFound
        global ${'two_current_page_id' . $pageUrlHash};

        if (!empty(${'two_current_page_id' . $pageUrlHash})) {
            return ${'two_current_page_id' . $pageUrlHash};
        }
        $home_url = rtrim(get_home_url(), '/');
        $page_url = rtrim((string) $page_url, '/');
        $id = 0;

        if (!empty($page_url)) {
            if (self::remove_url_protocol($home_url) === self::remove_url_protocol($page_url)) {
                return 'front_page';
            }
            $id = url_to_postid($page_url); // phpcs:ignore

            if ($id === 0 && class_exists('WooCommerce')) {
                $shop_page_id = wc_get_page_id('shop');
                $shop_page_url = rtrim(get_permalink(wc_get_page_id('shop')), '/');

                if ($shop_page_url === $page_url) {
                    $id = $shop_page_id;
                }
            }

            if ($id === 0) {
                $page_for_posts = get_option('page_for_posts');
                $post_page_id = (int) $page_for_posts;
                $post_page_url = rtrim(get_permalink($post_page_id), '/');

                if ($post_page_url === $page_url) {
                    $id = $post_page_id;
                }
            }

            if (0 === $id) {
                $page_headers = wp_get_http_headers(add_query_arg([ 'two_detect_post_id' => '1' ], $page_url));
                $id = isset($page_headers[ 'x-two-post-id' ]) ? $page_headers[ 'x-two-post-id' ] : 0;
            }
            ${'two_current_page_id' . $pageUrlHash} = $id;
        } else {
            ${'two_current_page_id' . $pageUrlHash} = self::get_current_post_info();
        }

        return ${'two_current_page_id' . $pageUrlHash};
    }

    public static function two_update_subscription()
    {
        $tenweb_subscription_id = false;
        $tenweb_plan_title = false;
        $domain_id = get_site_option('tenweb_domain_id');
        $access_token = get_site_option(TENWEB_PREFIX . '_access_token');
        $workspace_id = get_site_option(TENWEB_PREFIX . '_workspace_id');

        if (!empty($access_token) && !empty($domain_id) && !empty($workspace_id)) {
            $response = wp_remote_post(TENWEB_SO_CRITICAL_URL . '/v1/workspaces/' . $workspace_id . '/domains/' . $domain_id . '/get_subscription', [
                'timeout' => 5, // phpcs:ignore
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => [
                    'accept' => 'application/x.10webperformance.v1+json',
                    'authorization' => 'Bearer ' . $access_token,
                ],
                'body' => [],
                'cookies' => []
            ]);

            if (!is_wp_error($response) && isset($response['body'], $response['response']['code']) && $response['response']['code'] === 200) {
                $response_body = json_decode($response['body'], true);

                if ($response_body['data']['success']) {
                    if (isset($response_body['data']['subscription_id'])) {
                        $tenweb_subscription_id = $response_body['data']['subscription_id'];
                        \TenWebWpTransients\OptimizerTransients::set(TENWEB_PREFIX . '_subscription_id', $tenweb_subscription_id, 12 * HOUR_IN_SECONDS);
                    }

                    if (isset($response_body['data']['plan_title'])) {
                        $tenweb_plan_title = $response_body['data']['plan_title'];
                        $tenweb_plan_title = strtolower($tenweb_plan_title) == 'speed' ? 'Free' : $tenweb_plan_title; //sometimes we get 'speed' from service, it means free

                        if (in_array((int) $tenweb_subscription_id, TENWEB_SO_FREE_SUBSCRIPTION_IDS)) {
                            $tenweb_plan_title = 'Free';
                        }
                        \TenWebWpTransients\OptimizerTransients::set(TENWEB_PREFIX . '_plan_title', $tenweb_plan_title, 12 * HOUR_IN_SECONDS);
                    }

                    if (isset($response_body['data']['referral_hash'])) {
                        $referral_hash = $response_body['data']['referral_hash'];

                        if (!empty($referral_hash)) {
                            update_site_option(TENWEB_PREFIX . '_client_referral_hash', $referral_hash);
                        }
                    }
                }
            } elseif (is_wp_error($response) || !isset($response['body'], $response['response']['code']) || $response['response']['code'] !== 429) {
                \TenWebWpTransients\OptimizerTransients::set(TENWEB_PREFIX . '_subscription_id', '0', HOUR_IN_SECONDS);
                \TenWebWpTransients\OptimizerTransients::set(TENWEB_PREFIX . '_plan_title', '', HOUR_IN_SECONDS);
            }
        }

        return compact('tenweb_subscription_id', 'tenweb_plan_title');
    }

    public static function two_critical_status($page_id = false)
    {
        global $TwoSettings;
        $two_critical_pages = self::getCriticalPages();

        if (is_array($two_critical_pages)) {
            if ($page_id === false) {
                foreach ($two_critical_pages as $critical_page) {
                    self::two_critical_status($critical_page['id']);
                }
            } elseif (isset($two_critical_pages[$page_id])) {
                $critical_in_progress_key = 'two_critical_in_progress_' . $page_id;
                $critical_in_progress = \TenWebWpTransients\OptimizerTransients::get($critical_in_progress_key);

                if ($critical_in_progress !== '1') {
                    if (isset($two_critical_pages[$page_id]['status']) && $two_critical_pages[$page_id]['status'] == 'in_progress') {
                        $two_critical_pages[$page_id]['status'] = 'not_started';
                        $TwoSettings->update_setting('two_critical_pages', $two_critical_pages);
                    }
                }
            }
        }
    }

    public static function two_redirect($url, $exit = true)
    {
        while (ob_get_level() !== 0) {
            ob_end_clean();
        }
        wp_redirect($url); //phpcs:ignore WordPressVIPMinimum.Security.ExitAfterRedirect.NoExit

        if ($exit) {
            exit();
        }
    }

    public static function check_page_has_no_redirects($url, $arg = true)
    {
        if ($arg) {
            $url = add_query_arg([
                'two_check_redirect' => '1',
            ], $url);
        }
        $response = wp_remote_head($url, [
            'timeout' => 20, // phpcs:ignore
            'redirection' => 0,
            'httpversion' => '1.0',
            'blocking' => true,
            'sslverify' => false
        ]);

        if (!is_wp_error($response)) {
            $headers = $response[ 'headers' ];

            if (isset($headers[ 'Location' ])) {
                $location = $headers[ 'Location' ];

                if (!empty($location)) {
                    if (is_array($location)) {
                        $location = end($location);
                    }
                    $redirect_url_parse = wp_parse_url($location);
                    $main_url_parse = wp_parse_url($url);

                    if (isset($redirect_url_parse[ 'host' ]) && isset($main_url_parse[ 'host' ])) {
                        if ($redirect_url_parse[ 'host' ] === $main_url_parse[ 'host' ]) {
                            return true;
                        }
                    }

                    return false;
                }

                return false;
            }
        }

        return true;
    }

    public static function is_paid_user()
    {
        global $tenweb_subscription_id;

        return (defined('TENWEB_SO_HOSTED_ON_10WEB') && TENWEB_SO_HOSTED_ON_10WEB) || (defined('TENWEB_SO_FREE_SUBSCRIPTION_IDS') && (!in_array((int) $tenweb_subscription_id, TENWEB_SO_FREE_SUBSCRIPTION_IDS)));
    }

    public static function is_tenweb_booster_connected()
    {
        return (defined('TENWEB_SO_HOSTED_ON_10WEB') && TENWEB_SO_HOSTED_ON_10WEB) ||
            (defined('TENWEB_CONNECTED_SPEED')
                && \Tenweb_Authorization\Login::get_instance()->check_logged_in()
                && \Tenweb_Authorization\Login::get_instance()->get_connection_type() == TENWEB_CONNECTED_SPEED
                && !empty(get_option('two_first_connect')));
    }

    /**
     * For hosting cache , run only connected sites
     **/
    public static function update_post($id = 0)
    {
        if (TENWEB_SO_HOSTED_ON_10WEB) {
            return;
        }

        if ($id === 0) {
            $id = get_option('page_on_front');

            if ($id === '0' || $id === 0) {
                $recent_post = wp_get_recent_posts([
                    'numberposts' => 1,
                    'post_type' => 'post',
                    'post_status' => 'publish',
                ], OBJECT);

                if (is_array($recent_post) && isset($recent_post[0]->ID)) {
                    $id = $recent_post[0]->ID;
                }
            }
        }

        if ($id !== 0) {
            $post_data = ['ID' => $id];
            // removing kses_filter to avoid striping script or style tags from gutenberg editor
            remove_filter('content_save_pre', 'wp_filter_post_kses');
            wp_update_post(wp_slash($post_data));
            add_filter('content_save_pre', 'wp_filter_post_kses');
        }
    }

    /**
     * For not hosted sites
     **/
    public static function set_critical()
    {
        if (isset($_POST['token'], $_POST['page_id']) && get_option('two_critical' . sanitize_text_field($_POST['page_id'])) === $_POST['token']) { // phpcs:ignore
            \TenWebWpTransients\OptimizerTransients::delete('two_critical' . sanitize_text_field($_POST['page_id'])); // phpcs:ignore

            if (isset($_FILES['covered_css']) && isset($_FILES['covered_css']['tmp_name'])) {
                //TODO: maybe sanitization must be implemented
                $uploadfile = $_FILES['covered_css']['tmp_name']; // phpcs:ignore
                \TenWebWpTransients\OptimizerTransients::delete('two_critical_in_process');
                $triggerPostOptimizationTasks = !empty($_POST['newly_connected_website']) && !empty($_POST['notification_id']); // phpcs:ignore
                update_option('two_critical_data_import_data_' . time(), $triggerPostOptimizationTasks, false);
                \TenWebOptimizer\OptimizerCriticalCss::createCriticalCSS($uploadfile, $triggerPostOptimizationTasks);
                echo '{"status":"ok"}';
                die(0);
            }

            die('no covered_css data');
        }

        die('Invalid token');
    }

    public static function download_critical()
    {
        if (isset($_GET['two_update_critical'], $_GET['page_id']) && $_GET['two_update_critical'] === '1') { // phpcs:ignore
            $return_data = [
                'success' => false,
                'message' => 'error'
            ];
            $page_id = sanitize_text_field($_GET['page_id']); // phpcs:ignore
            $triggerPostOptimizationTasks = !empty($_GET['newly_connected_website']) && !empty($_GET['notification_id']); // phpcs:ignore
            $domain_id = get_site_option('tenweb_domain_id');
            $access_token = get_site_option(TENWEB_PREFIX . '_access_token');
            $file_content_response = wp_remote_get(TENWEB_SO_CRITICAL_URL . '/v1/critical/' . $domain_id . '/pages/' . $page_id . '/get', [ // phpcs:ignore
                'timeout' => 5, // phpcs:ignore
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => [
                    'accept' => 'application/x.10webperformance.v1+json',
                    'authorization' => 'Bearer ' . $access_token,
                ],
                'cookies' => []
            ]);

            if (!is_wp_error($file_content_response) && isset($file_content_response['body'])) {
                $file_content = $file_content_response['body'];
                $file_content_arr = json_decode($file_content, true);

                if (isset($file_content_arr['data']['data']['covered_css']['value'])) {
                    \TenWebOptimizer\OptimizerCriticalCss::createCriticalCSS(false, $triggerPostOptimizationTasks, $file_content_arr['data']['data']['covered_css']['value'], true);
                    $return_data['success'] = true;
                    $return_data['message'] = 'success';
                }
            }
            echo json_encode($return_data); // phpcs:ignore
            die;
        }
    }

    public static function update_connection_flow_progress($status, $step, $metaData = [])
    {
        $flow_id = get_site_option(TENWEB_PREFIX . '_flow_id');
        $notification_id = get_site_option(TENWEB_PREFIX . '_notification_id');
        $domain_id = get_site_option('tenweb_domain_id');
        $access_token = get_site_option(TENWEB_PREFIX . '_access_token');

        if ($access_token && $domain_id) {
            $response = wp_remote_post(TENWEB_SO_CRITICAL_URL . '/v1/domains/' . $domain_id . '/update_connection_flow_progress', [
                'timeout' => 89, // phpcs:ignore
                'redirection' => 15,
                'blocking' => true,
                'headers' => [
                    'accept' => 'application/x.10webperformance.v1+json',
                    'authorization' => 'Bearer ' . $access_token,
                ],
                'body' => [
                    'flow_id' => $flow_id,
                    'notification_id' => $notification_id,
                    'status' => $status,
                    'step' => $step,
                    'debug_data' => $metaData
                ],
                'cookies' => []
            ]);
        }
    }

    public static function filter_incompatible_plugins($plugins)
    {
        $two_incompatible_plugin_list = self::TWO_INCOMPATIBLE_PLUGIN_LIST;
        $return_list = [
            'incompatible' => [],
            'compatible' => [],
        ];

        foreach ($plugins as $plugin) {
            $key = array_search($plugin, $two_incompatible_plugin_list, true);

            if ($key) {
                $return_list['incompatible'][] = $two_incompatible_plugin_list[$key];
            } else {
                $return_list['compatible'][] = $plugin;
            }
        }

        return $return_list;
    }

    public static function get_conflicting_plugins()
    {
        $two_incompatible_plugin_list = self::TWO_INCOMPATIBLE_PLUGIN_LIST;
        $active_plugins = get_option('active_plugins');
        $all_plugins = get_plugins();

        $incompatible_active_plugin_slugs = array_intersect($two_incompatible_plugin_list, $active_plugins);
        $incompatible_active_plugin_list = [];

        foreach ($incompatible_active_plugin_slugs as $plugin) {
            $incompatible_active_plugin_list[$plugin] = $all_plugins[$plugin]['Name'];
        }

        return $incompatible_active_plugin_list;
    }

    public static function injectCriticalBg($content, $critical, $cacheStructure)
    {
        if (isset($_GET['no_critical_css']) && $_GET['no_critical_css'] == 1) { // phpcs:ignore
            return $content;
        }
        global $TwoSettings;
        $two_serve_optimized_bg_image = $TwoSettings->get_settings('two_serve_optimized_bg_image');

        if ($two_serve_optimized_bg_image === 'on') {
            if (isset($critical) && isset($critical->critical_bg) && !empty($critical->critical_bg)) {
                $critical_bg_file_dir = TWO_CACHE_DIR . 'critical/' . $critical->critical_bg;

                if (file_exists($critical_bg_file_dir)) {
                    $critical_bg_data = file_get_contents($critical_bg_file_dir); // phpcs:ignore
                    $critical_bg_data_arr = json_decode($critical_bg_data, true);
                    $critical_bg_css = self::serve_different_sizes_for_critical_bg_image($critical_bg_data_arr);

                    if (!empty($critical_bg_css)) {
                        $critical_bg_css = "<style id='two_critical_bg' class='two_critical_bg'>" . $critical_bg_css . '</style>';
                    }
                }
            }
        }

        if (isset($critical_bg_css) && !empty($critical_bg_css)) {
            $content = OptimizerUtils::inject_in_html($content, $critical_bg_css, ['</head>', 'before']);
            $cacheStructure->addToTagsToAdd($critical_bg_css, ['</head>', 'before']);
        }

        return $content;
    }

    public static function delete_define($key, $content)
    {
        $re = '/define\s*\(\s*[\'\"](' . $key . ')[\'\"].?,(.*?)\);/';
        preg_match_all($re, $content, $matches, PREG_SET_ORDER, 0);

        if (is_array($matches)) {
            foreach ($matches as $define_arr) {
                if (isset($define_arr[0], $define_arr[1]) && $define_arr[1] == 'WP_CACHE') {
                    $content = str_replace($define_arr[0], '', $content);
                }
            }
        }

        return $content;
    }

    public static function check_plugin_update()
    {
        $plugins = get_site_transient('update_plugins');

        if (isset($plugins->response) && is_array($plugins->response)) {
            foreach ($plugins->response as $plugin_data) {
                if (isset($plugin_data->slug) && $plugin_data->slug === 'tenweb-speed-optimizer') {
                    return true;
                }
            }
        }

        return false;
    }

    public static function clear_cloudflare_cache($prefixes = [], $clear_cache_from = '')
    {
        global $TwoSettings;
        $cloudflare_cache_status = $TwoSettings->get_settings('cloudflare_cache_status');

        if (($cloudflare_cache_status !== 'on' || TENWEB_SO_HOSTED_ON_10WEB) && $clear_cache_from != 'settings_page') {
            return true;
        }
        $domain_id = get_site_option('tenweb_domain_id');
        $access_token = get_site_option(TENWEB_PREFIX . '_access_token');
        $workspace_id = get_site_option(TENWEB_PREFIX . '_workspace_id');

        $req_body = [];
        $req_body['prefixes'] = $prefixes;

        if (isset($access_token, $domain_id, $workspace_id) && !empty($access_token) && !empty($domain_id) && !empty($workspace_id)) {
            $response = wp_remote_post(TENWEB_SO_CRITICAL_URL . '/v1/workspaces/' . $workspace_id . '/domains/' . $domain_id . '/clear_cloudflare_cache', [
                'timeout' => 1,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => false,
                'headers' => [
                    'accept' => 'application/x.10webperformance.v1+json',
                    'authorization' => 'Bearer ' . $access_token,
                ],
                'body' => $req_body,
                'cookies' => []
            ]);
        }

        return true;
    }

    public static function has_changed_bg_image()
    {
        return self::$has_changed_bg_image;
    }

    public static function delete_critical_page($page_id)
    {
        global $TwoSettings;
        $critical_key = 'two_critical_' . $page_id;
        $critical_in_progress_key = 'two_critical_in_progress_' . $page_id;
        \TenWebWpTransients\OptimizerTransients::delete($critical_key);
        \TenWebWpTransients\OptimizerTransients::delete($critical_in_progress_key);

        $two_critical_all_pages = OptimizerUtils::getCriticalPages();

        if (OptimizerUrl::isCriticalSavedInSettings($page_id)) {
            $two_critical_pages = $TwoSettings->get_settings('two_critical_pages');
            unset($two_critical_pages[$page_id]);
            unset($two_critical_pages['']);
            $TwoSettings->update_setting('two_critical_pages', $two_critical_pages);
        } else {
            delete_post_meta($page_id, 'two_critical_pages');
        }
        $prefix = 'critical/two_' . $page_id . '_*.*';
        self::delete_files_by_prefix($prefix);

        $tenweb_subscription_id = \TenWebWpTransients\OptimizerTransients::get(TENWEB_PREFIX . '_subscription_id');
        $is_free = (in_array((int) $tenweb_subscription_id, TENWEB_SO_FREE_SUBSCRIPTION_IDS) && !TENWEB_SO_HOSTED_ON_10WEB);

        if (is_array($two_critical_all_pages) && isset($two_critical_all_pages[$page_id]) && $is_free) {
            self::delete_so_page($page_id);
        }
    }

    public static function delete_so_page($page_id)
    {
        $domain_id = get_site_option('tenweb_domain_id');
        $access_token = get_site_option(TENWEB_PREFIX . '_access_token');
        $workspace_id = get_site_option(TENWEB_PREFIX . '_workspace_id');

        if ($access_token && $domain_id && $workspace_id) {
            wp_remote_post(TENWEB_SO_CRITICAL_URL . '/v1/workspaces/' . $workspace_id . '/domains/' . $domain_id . '/delete-so-page', [
                'timeout' => 5, // phpcs:ignore
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => [
                    'accept' => 'application/x.10webperformance.v1+json',
                    'authorization' => 'Bearer ' . $access_token,
                ],
                'body' => [
                    'pageId' => $page_id
                ],
                'cookies' => []
            ]);
        }
    }

    public static function update_site_state()
    {
        $domain_id = get_option('tenweb_domain_id');

        if ($domain_id) {
            global $wp_version, $wpdb;
            $sql_version = $wpdb->get_var('SELECT VERSION() AS version'); // phpcs:ignore
            $home_url = get_home_url();
            $admin_url = get_admin_url();
            $site_title = get_bloginfo('name');
            $url = TENWEB_API_URL . '/site-state/' . $domain_id;
            $site_info = [
                'site_info' => [
                    'platform' => 'wordpress',
                    'site_url' => $home_url,
                    'admin_url' => $admin_url,
                    'name' => $home_url,
                    'site_title' => $site_title,
                    'site_screenshot_url' => $home_url,
                    'platform_version' => $wp_version,
                    'php_version' => PHP_VERSION,
                    'mysql_version' => $sql_version,
                    'manager_version' => get_site_option(TENWEB_PREFIX . '_from_image_optimizer') ? 'iowd_' . TENWEBIO_PREFIX : TENWEB_VERSION,
                    'other_data' => [
                        'manager_version' => TENWEB_VERSION,
                    ]
                ]
            ];
            $args = [
                'method' => 'POST',
                'body' => ['data' => $site_info]
            ];
            $Helper = \Tenweb_Authorization\Helper::get_instance();

            return $Helper->request($url, $args, 'send_site_state');
        }
    }

    public static function init_flow_score_check($only_do_request = false)
    {
        if ($only_do_request) {
            $nonce = uniqid('two_', false);
            update_option('wp_two_nonce_two_init_flow_score', $nonce);
            $res = wp_remote_post(admin_url('admin-ajax.php'), [
                'timeout' => 5, // phpcs:ignore
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => false,
                'body' => [
                    'action' => 'two_init_flow_score',
                    'nonce' => $nonce
                ],
                'cookies' => []
            ]);

            return;
        }
        $flow_score_check_init = get_option('flow_score_check_init', false);
        $two_flow_speed = get_option('two_flow_speed', false);

        if ($flow_score_check_init !== '1' && !is_array($two_flow_speed) && !\Tenweb_Authorization\Login::get_instance()->check_logged_in()) {
            update_option('flow_score_check_init', '1');
            self::add_log_for_score_check_flow('init_flow_score_check', 'start two_google_check_score');
            \TenWebSC\TWScoreChecker::twsc_check_score('front_page', $old = true, $no_optimized = true);
            $score_data = get_option('two-front-page-speed');
            $speed_data['nooptimize_score'] = [];

            if (isset($score_data['previous_score']['desktop_score'], $score_data['previous_score']['desktop_tti'])) {
                $speed_data['nooptimize_score']['desktopScore'] = $score_data['previous_score']['desktop_score'];
                $speed_data['nooptimize_score']['desktopTti'] = $score_data['previous_score']['desktop_tti'];
            }

            if (isset($score_data['previous_score']['mobile_score'], $score_data['previous_score']['mobile_tti'])) {
                $speed_data['nooptimize_score']['mobileScore'] = $score_data['previous_score']['mobile_score'];
                $speed_data['nooptimize_score']['mobileTti'] = $score_data['previous_score']['mobile_tti'];
            }
            $speed_data['nooptimize_score']['tool'] = 'pageSpeedInsight';
            $speed_data['nooptimize_score']['two_version'] = TENWEB_SO_VERSION;
            $speed_data['nooptimize_score']['desktopData'] = [];
            $speed_data['nooptimize_score']['mobileData'] = [];

            update_site_option('two_flow_speed', $speed_data);
        }
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare("SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", TENWEB_PREFIX . '_access_token')); // phpcs:ignore
        $access_token = false;

        if (is_object($row) && isset($row->option_value)) {
            $access_token = $row->option_value;
        }
        $two_flow_speed = get_option('two_flow_speed', false);
        self::add_log_for_score_check_flow('two_flow_speed', $two_flow_speed);

        if ($access_token && is_array($two_flow_speed) && isset($two_flow_speed['nooptimize_score']['mobileScore'], $two_flow_speed['nooptimize_score']['desktopScore'])) {
            $domain_id = get_site_option('tenweb_domain_id');
            $flow_id = get_site_option(TENWEB_PREFIX . '_flow_id', false);

            $workspace_id = get_site_option(TENWEB_PREFIX . '_workspace_id');
            $route = TENWEB_SO_CRITICAL_URL . '/v1/workspaces/' . $workspace_id . '/domains/' . $domain_id . '/set_nooptimize_score';
            $body = $two_flow_speed;
            $body['check_with_no_optimization'] = true;
            $body['flow_id'] = $flow_id;
            $body['is_first_optimization_flow'] = false;
            self::add_log_for_score_check_flow('send_data_to_performance', 'start send_data_to_performance');
            self::send_data_to_performance($route, $body, $access_token);
        }
    }

    public static function send_data_to_performance($route, $body, $access_token)
    {
        $res = wp_remote_post($route, [
            'timeout' => 10, // phpcs:ignore
            'redirection' => 10,
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => [
                'accept' => 'application/x.10webperformance.v1+json',
                'authorization' => 'Bearer ' . $access_token,
            ],
            'body' => $body,
            'cookies' => []
        ]);
    }

    public static function add_log_for_score_check_flow($key, $val)
    {
        $two_flow_score_log = get_option('two_flow_score_log', []);
        $flag = 1;

        if (isset($two_flow_score_log['flag'])) {
            $flag = (int) $two_flow_score_log['flag'];
            $flag ++;
        }
        $arr_key = $flag . '_' . $key;
        $two_flow_score_log[$arr_key] = $val;
        $two_flow_score_log['flag'] = $flag;
        update_option('two_flow_score_log', $two_flow_score_log);
    }

    public static function warmup_cache()
    {
        if (!self::check_if_hosted_website()) {
            $site_url = site_url() . '?tenweb_warmup_cache=1&tenweb_version=' . TENWEB_SO_VERSION . '&warmup_time=' . time();
            wp_remote_get($site_url, ['sslverify' => false, 'blocking' => false, 'timeout' => 0.1]); // phpcs:ignore
        }
    }

    public static function get_absolute_url($url)
    {
        $parsed = parse_url($url); // phpcs:ignore
        $path = $parsed['path'];

        if (!str_contains($path, '..')) {
            return $url;
        }

        // fix dots in relative path
        $path_parts = explode('/', $path);
        $absolutes = [];

        foreach ($path_parts as $part) {
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }

        return str_replace($parsed['path'], implode('/', $absolutes), $url);
    }

    public static function IOConnected()
    {
        return get_site_option(TENWEB_PREFIX . '_from_image_optimizer');
    }

    public static function TWOConnected()
    {
        return get_option('two_first_connect');
    }

    public static function request_webp_action($task, $url_list = '')
    {
        try {
            if ('regenerate' === $task) {
                $image_list = [];
                $page_list = [];

                foreach (explode(' ', $url_list) as $url) {
                    if (0 === strpos($url, site_url())) {
                        if (preg_match('/\.(jpg|png|jpeg)$/', $url)) {
                            $image_list[] = $url;
                        } else {
                            $page_list[] = $url . (strpos($url, '?') > -1 ? '&' : '?') . 'two_nooptimize=1';
                        }
                    }
                }

                if (empty($image_list) || TENWEB_SO_HOSTED_ON_10WEB) {
                    $request_data = [
                        'force_convert' => 0,
                        'quality' => 50,
                        'image_list' => implode(',', $image_list),
                        'url_list' => implode(',', $page_list),
                        'site_url' => site_url(),
                    ];
                    $method = 'POST';
                    $endpoint = \TenWebIO\Api::API_WEBP_CONVERT;
                    $api_instance = new \TenWebIO\Api($endpoint);
                    $response = $api_instance->apiRequest($method, $request_data);

                    if (false !== $response) {
                        $response_data = [
                            'status' => 'success',
                        ];
                    } else {
                        $response_data = [
                            'status' => 'error',
                            'error' => false
                        ];
                    }
                } else {
                    //if we have array of urls, and website is not hosted on 10Web call internal IO classes to optimize them
                    $compressService = new CompressService();
                    $compressService->compressCustom($image_list, 'front_page', 1);
                    $response_data = [
                        'status' => 'success',
                    ];
                }
            } elseif ('delete' === $task) {
                $count = \TenWebIO\Utils::deleteWebPImages();
                $response_data = [
                    'status' => 'success',
                    'count' => $count
                ];
            } else {
                $response_data = [
                    'status' => 'error',
                    'error' => 'Invalid Task'
                ];
            }
        } catch (Exception $e) {
            $response_data = [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }

        return $response_data; // phpcs:ignore
    }

    public static function check_admin_capabilities()
    {
        return current_user_can('manage_options');
    }

    public static function dirsize($dir)
    {
        @$dh = opendir($dir);
        $size = 0;

        if ($dh) {
            while ($file = @readdir($dh)) { // phpcs:ignore
                if ($file != '.' and $file != '..') {
                    $path = $dir . '/' . $file;

                    if (is_dir($path)) {
                        $size += self::dirsize($path); // recursive in sub-folders
                    } elseif (is_file($path)) {
                        $size += filesize($path); // add file
                    }
                }
            }
            @closedir($dh);
        }

        return $size;
    }
}
