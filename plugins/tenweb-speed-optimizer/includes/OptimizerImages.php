<?php

namespace TenWebOptimizer;

/*
 * Handles optimizing images.
 */
if (!defined('ABSPATH')) {
    exit;
}

class OptimizerImages
{
    const TWO_SRC_ATTRIBUTE_VALUE_REGEXP = 'src=["\']([^"\'].+?)["\']|srcset=["\']([^"\'].+?)["\']';

    const TWO_TYPE_ATTRIBUTE_VALUE_REGEXP = 'type=["\']([^"\'].+?)["\']';

    const TWO_AUDIO_MARKER = 'TWO_AUDIO_MARKER ';

    const TWO_EXCLUDE_LAZY_KEYWORDS = [
        'image-compare__'
    ];

    /**
     * Options.
     *
     * @var array
     */
    private $two_iframe_lazyload = 'off';

    private $two_youtube_vimeo_iframe_lazyload = 'off';

    private $browser_lazy = false;

    private $vanilla_lazy = false;

    private $lazy_class = 'lazyload ';

    private $smart_lazy_load_data = [];

    /**
     * Singleton instance.
     *
     * @var self|null
     */
    protected static $instance = null;

    private $TwoSettings;

    public function __construct($options = [])
    {
        global $TwoSettings;
        $this->TwoSettings = $TwoSettings;
        $lazy_load_type = $this->TwoSettings->get_settings('lazy_load_type');

        if ($lazy_load_type === 'browser') {
            $this->browser_lazy = true;
        }

        if ($lazy_load_type === 'vanilla') {
            $this->vanilla_lazy = true;
            $this->lazy_class = 'lazy ';
        }

        if (isset($options['two_iframe_lazyload'])) {
            $this->two_iframe_lazyload = $options['two_iframe_lazyload'];
        }

        if (isset($options['two_youtube_vimeo_iframe_lazyload'])) {
            $this->two_youtube_vimeo_iframe_lazyload = $options['two_youtube_vimeo_iframe_lazyload'];
        }
    }

    public static function instance($options = [])
    {
        if (null === self::$instance) {
            self::$instance = new self($options);
        }

        return self::$instance;
    }

    public function run()
    {
        $this->run_on_frontend();
    }

    public function run_on_frontend()
    {
        add_filter('twoptimize_html_after_minify', [$this, 'filter_lazyload_images'], 10, 1);
        add_filter('twoptimize_html_after_minify_iframe', [$this, 'filter_lazyload_iframes'], 10, 1);
        add_filter('twoptimize_html_after_minify_video', [$this, 'filter_lazyload_video'], 10, 1);
        add_filter('twoptimize_html_images', [$this, 'filter_optimize_html_images'], 10, 1);
    }

    public function get_size_from_tag($tag)
    {
        // reusable function to extract widht and height from an image tag
        // enforcing a filterable maximum width and height (default 4999X4999).
        $width = '';
        $height = '';

        if (preg_match('#width=("|\')(.*)("|\')#Usmi', $tag, $_width)) {
            if (strpos($_width[2], '%') === false) {
                $width = (int) $_width[2];
            }
        }

        if (preg_match('#height=("|\')(.*)("|\')#Usmi', $tag, $_height)) {
            if (strpos($_height[2], '%') === false) {
                $height = (int) $_height[2];
            }
        }
        // check for and enforce (filterable) max sizes.
        $_max_width = 4999;

        if ($width > $_max_width) {
            $_width = $_max_width;
            $height = $_width / $width * $height;
            $width = $_width;
        }
        $_max_height = 4999;

        if ($height > $_max_height) {
            $_height = $_max_height;
            $width = $_height / $height * $width;
            $height = $_height;
        }

        return [
            'width' => $width,
            'height' => $height,
        ];
    }

    public function should_lazyload($context = '')
    {
        return true;
    }

    public function filter_optimize_html_images($in)
    {
        $to_replace = [];
        // hide (no)script tags to avoid nesting noscript tags (as lazyloaded images add noscript).
        $out = OptimizerBase::replace_contents_with_marker_if_exists('SCRIPT', '<script', '#<(?:no)?script.*?<\/(?:no)?script>#is', $in);

        // extract img tags and add lazyload attribs.
        if (preg_match_all('#(<img[^>]*src[^>]*>)#Usmi', $out, $matches)) {
            foreach ($matches[0] as $tag) {
                // phpcs:ignore Squiz.PHP.CommentedOutCode.Found
                //if ( $this->should_lazyload($out) ) {
                $to_replace[$tag] = $this->disable_pagespeed_image_optimization($tag);
                // phpcs:ignore Squiz.PHP.CommentedOutCode.Found
                //}
            }
            $out = str_replace(array_keys($to_replace), array_values($to_replace), $out);
        }
        // restore noscript tags.
        $out = OptimizerBase::restore_marked_content('SCRIPT', $out);

        return $out;
    }

    public function filter_lazyload_images($in)
    {
        $to_replace = [];
        // hide (no)script tags to avoid nesting noscript tags (as lazyloaded images add noscript).
        $out = OptimizerBase::replace_contents_with_marker_if_exists('SCRIPT', '<script', '#<(?:no)?script.*?<\/(?:no)?script>#is', $in);
        // extract img tags and add lazyload attribs.

        preg_match_all('#<audio\s*.*>\s*.*<\/audio>#Usmi', $out, $matches);
        $audio_to_replace = [];

        foreach ($matches as $tag) {
            if (!empty($tag[0])) {
                $audio = $tag[0];
                $audio_to_replace[$audio] = str_replace('<source ', '<source ' . self::TWO_AUDIO_MARKER, $audio);
            }
        }

        $out = str_replace(array_keys($audio_to_replace), array_values($audio_to_replace), $out);

        if (preg_match_all('#<img[^>]*src[^>]*>|<source[^>]*src[^>]*>#Usmi', $out, $matches)) {
            foreach ($matches[0] as $tag) {
                if ($this->should_lazyload($out) && strpos($tag, self::TWO_AUDIO_MARKER) === false) {
                    $to_replace[$tag] = $this->image_lazyload($tag);
                }
            }
            $out = str_replace(array_keys($to_replace), array_values($to_replace), $out);
        }
        // restore noscript tags.
        $out = OptimizerBase::restore_marked_content('SCRIPT', $out);
        $out = str_replace(self::TWO_AUDIO_MARKER, '', $out);

        return $out;
    }

    public function filter_lazyload_iframes($in)
    {

        // only used is image optimization is NOT active but lazyload is.
        $to_replace = [];
        // hide (no)script tags to avoid nesting noscript tags (as lazyloaded images add noscript).
        $out = OptimizerBase::replace_contents_with_marker_if_exists('SCRIPT', '<script', '#<(?:no)?script.*?<\/(?:no)?script>#is', $in);
        // extract img tags and add lazyload attribs.

        if (preg_match_all('#<iframe[^>]*src[^>]*>#Usmi', $out, $matches)) {
            // only used is image optimization is NOT active but lazyload is.
            foreach ($matches[0] as $tag) {
                if ($this->should_lazyload($out)) {
                    $to_replace[$tag] = $this->add_lazyload($tag);
                }
            }

            $out = str_replace(array_keys($to_replace), array_values($to_replace), $out);
        }
        // restore noscript tags.
        $out = OptimizerBase::restore_marked_content('SCRIPT', $out);

        return $out;
    }

    public function filter_lazyload_video($in)
    {

        // only used is image optimization is NOT active but lazyload is.
        $to_replace = [];
        // hide (no)script tags to avoid nesting noscript tags (as lazyloaded images add noscript).
        $out = OptimizerBase::replace_contents_with_marker_if_exists('SCRIPT', '<script', '#<(?:no)?script.*?<\/(?:no)?script>#is', $in);

        // extract img tags and add lazyload attribs.
        //<video[^>]*>(.*?)</video>
        if (preg_match_all('#<video[^>]*>((.*?\n*+)+)<\/video>#', $out, $matches)) {
            // only used is image optimization is NOT active but lazyload is.
            foreach ($matches[0] as $tag) {
                if ($this->should_lazyload($out)) {
                    $to_replace[$tag] = $this->add_lazyload($tag);
                }
            }
            $out = str_replace(array_keys($to_replace), array_values($to_replace), $out);
        }
        // restore noscript tags.
        $out = OptimizerBase::restore_marked_content('SCRIPT', $out);

        return $out;
    }

    public function add_lazyload($tag, $placeholder = '')
    {
        if ($this->isExcludedTag($tag)) {
            return $tag;
        }

        if (empty($this->TwoSettings->get_settings('two_lazyload_slider_images', ''))) {
            /*
                   * keywords for popular sliders
                   * exclude slider images
                   * */
            $slider_keywords = [
                'swiper-slide',
                'slider',
                'soliloquy',
                'rev-slide',
                'rev-slidebg'
            ];

            foreach ($slider_keywords as $val) {
                if (strpos($tag, $val) !== false) {
                    return $tag;
                }
            }
        }

        preg_match('@' . self::TWO_SRC_ATTRIBUTE_VALUE_REGEXP . '@', $tag, $match);
        $src = array_pop($match);

        if (!isset($src) || $src === '') {
            return $tag;
        }

        if ($this->isExcluded($tag, 'two_exclude_lazyload')) {
            return $tag;
        }

        /*
         * end excluding images for sliders
         * */

        // adds actual lazyload-attributes to an image node.
        if (str_ireplace($this->get_lazyload_exclusions(), '', $tag) === $tag) {

            // store original tag for use in noscript version.
            $noscript_tag = '<noscript>' . $tag . '</noscript>';

            // insert lazyload class.
            if (!$this->browser_lazy) {
                //lazyload
                $tag = self::inject_classes_in_tag($tag, $this->lazy_class);

                if (!$placeholder || empty($placeholder)) {
                    $placeholder = $this->generatePlaceholder($tag);
                }
            }

            if ($this->isIframe($tag)) {
                $tag = $this->replace_iframe($tag, $src);
            } elseif ($this->isVideo($tag)) {
                $tag = $this->replace_video($tag);
            } else {
                $tag = $this->replace_image($tag, $placeholder);
            }
            $pos_srcset = strpos($tag, 'data-srcset=');

            if (!$pos_srcset) {
                $tag = str_replace('srcset=', ' data-srcset=', $tag);
            }
            $pos_sizes = strpos($tag, 'data-sizes=');

            if (!$pos_sizes) {
                $tag = str_replace('sizes=', ' data-sizes=', $tag);
            }
            // add the noscript-tag from earlier.
            $two_add_noscript = empty($this->TwoSettings->get_settings('two_add_noscript', 'off')) ? 'off' : 'on';

            if ($two_add_noscript == 'on') {
                $tag = $noscript_tag . $tag;
            }
        }

        return $tag;
    }

    private function generatePlaceholder($tag)
    {
        // get image width & heigth for placeholder fun (and to prevent content reflow).
        $_get_size = $this->get_size_from_tag($tag);
        $width = $_get_size['width'];
        $height = $_get_size['height'];

        if (false === $width) {
            $widht = 210; // default width for SVG placeholder.
        }

        if (false === $height) {
            $heigth = $width / 3 * 2; // if no height, base it on width using the 3/2 aspect ratio.
        }
        // insert the actual lazyload stuff.
        // see https://css-tricks.com/preventing-content-reflow-from-lazy-loaded-images/ for great read on why we're using empty svg's.
        $placeholder = $this->get_default_lazyload_placeholder($width, $height);

        return $placeholder;
    }

    private function replace_iframe($tag, $src)
    {
        $video_id = null;
        $class_name = null;

        $this->smart_lazy_load_data('iframe');

        if ($this->browser_lazy) {
            $tag = str_replace('<iframe', '<iframe loading="lazy"', $tag);
        } else {
            if ($this->two_youtube_vimeo_iframe_lazyload === 'on') {
                preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $src, $match);

                if (!empty($match[1])) {
                    $video_id = $match[1];
                    $class_name = 'yt-lazyload';
                } else {
                    preg_match('%^https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)(?:[?]?.*)$%im', $src, $match);

                    if (!empty($match[3])) {
                        $video_id = $match[3];
                        $class_name = 'vi-lazyload';
                    }
                }
            }

            if ($video_id && $class_name) {
                $tag = '<div class="' . $class_name . '" data-id="' . $video_id . '" data-thumb="" data-logo="2"></div>';
                $this->smart_lazy_load_data('two_youtube_vimeo_iframe_lazyload');
            } else {
                $tag = str_replace('src=', ' src="" data-src=', $tag);
            }
        }

        return $tag;
    }

    private function replace_video($tag)
    {
        $pos = strpos($tag, 'data-src=');

        if ($this->browser_lazy) {
            $tag = str_replace('<video', '<video preload="none"', $tag);
        } elseif ($this->vanilla_lazy && !$pos) {
            $tag = str_replace('src', 'data-src', $tag);

            if (false === strpos($tag, 'data-poster=')) {
                $tag = str_replace('poster=', 'data-poster=', $tag);
            }
        } elseif (!$pos) {
            /* Get src from source tag*/
            preg_match_all('@' . self::TWO_SRC_ATTRIBUTE_VALUE_REGEXP . '@', $tag, $match);
            $src = array_pop($match);

            /* Get type from source tag*/
            preg_match_all('@' . self::TWO_TYPE_ATTRIBUTE_VALUE_REGEXP . '@', $tag, $match);
            $type = array_pop($match);

            /* Get data-src attr accoording lib requirements */
            $data_src = '';
            $srcCount = count($src);

            for ($i = 0; $i < $srcCount; $i++) {
                $t = explode('.', $src[$i]);

                if (!isset($type[$i])) {
                    $type[$i] = 'video/' . end($t);
                }
                $data_src .= $src[$i] . '|' . $type[$i] . ',';
            }
            $data_src = rtrim($data_src, ',');

            $tag = str_replace('<video', '<video data-src=' . $data_src, $tag);

            if (false === strpos($tag, 'data-poster=')) {
                $tag = str_replace('poster=', 'data-poster=', $tag);
            }
            /* Remove source tags inside the video tag */
            $tag = preg_replace("/<source[^>]+\>/i", ' ', $tag);
        }

        return $tag;
    }

    private function replace_image($tag, $placeholder)
    {
        $pos = strpos($tag, 'data-src=');

        if ($this->browser_lazy) {
            $tag = str_replace('<img', '<img loading="lazy"', $tag);
        } elseif (!$pos) {
            $tag = str_replace(' src=', ' src=\'' . $placeholder . '\' data-src=', $tag);
        }

        return $tag;
    }

    public function add_lazyload_for_images_pagespeed($tag)
    {
        //this one is reverse, if image is excluded or option is disabled we add attribute
        $two_lazyload = empty($this->TwoSettings->get_settings('two_lazyload', 'off')) ? 'off' : 'on';

        if ($this->isImage($tag) && ($this->isExcluded($tag, 'two_exclude_lazyload') || $two_lazyload === 'off')) {
            return str_replace(' src=', ' ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' src=', $tag);
        }

        return $tag;
    }

    public function disable_optimisation_for_images_pagespeed($tag)
    {
        //this one is reverse, if image is excluded or option is disabled we add attribute
        $two_do_not_optimize_images = empty($this->TwoSettings->get_settings('two_do_not_optimize_images', 'off')) ? 'off' : 'on';

        if ($this->isImage($tag) && ($this->isExcluded($tag, 'two_exclude_images_for_optimize') || $two_do_not_optimize_images === 'on')) {
            return str_replace(' src=', ' data-pagespeed-no-transform src=', $tag);
        }

        return $tag;
    }

    private function isImage($tag)
    {
        return strpos($tag, '<img') !== false;
    }

    private function isIframe($tag)
    {
        return strpos($tag, '<iframe') !== false;
    }

    private function isVideo($tag)
    {
        return strpos($tag, '<video') !== false;
    }

    public function image_lazyload($tag)
    {
        if (OptimizerUtils::is_pagespeed_lazyload_enabled()) {
            return $this->add_lazyload_for_images_pagespeed($tag);
        }

        return $this->add_lazyload($tag);
    }

    public function disable_pagespeed_image_optimization($tag)
    {
        if (OptimizerUtils::is_pagespeed_image_optimization_enables()) {
            return $this->disable_optimisation_for_images_pagespeed($tag);
        }

        return $tag;
    }

    public function isExcluded($tag, $option_name)
    {
        /*
         * keywords for popular sliders
         * exclude slider images
         * */

        $slider_keywords = [
            'soliloquy',
        ];

        foreach ($slider_keywords as $val) {
            if (strpos($tag, $val) !== false) {
                return true;
            }
        }
        preg_match('@' . self::TWO_SRC_ATTRIBUTE_VALUE_REGEXP . '@', $tag, $match);
        $src = array_pop($match);

        if (!isset($src) || $src === '') {
            return true;
        }
        $two_exclude_tag = $this->TwoSettings->get_settings($option_name);
        global $TwoSettings;
        $two_img_in_viewport_lazyload = $TwoSettings->get_settings('two_img_in_viewport_lazyload');

        if ($two_img_in_viewport_lazyload == 'on') {
            $critical = new OptimizerCriticalCss();

            if (!empty($critical->images_in_viewport) && is_array($critical->images_in_viewport)) {
                $two_exclude_tag .= ',' . implode(',', $critical->images_in_viewport);
            }
        }

        if (isset($two_exclude_tag) && !empty($two_exclude_tag)) {
            $exclude_tag = explode(',', $two_exclude_tag);

            foreach ($exclude_tag as $name) {
                if (!empty($name) && strpos($src, $name) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    public function isExcludedTag($tag)
    {
        foreach (self::TWO_EXCLUDE_LAZY_KEYWORDS as $val) {
            if (strpos($tag, $val) !== false) {
                return true;
            }
        }

        return false;
    }

    public function get_lazyload_exclusions()
    {
        return [];
    }

    public static function inject_classes_in_tag($tag, $target_class)
    {
        if (strpos($tag, 'class=') !== false) {
            $tag = preg_replace('/(\sclass\s?=\s?("|\'))/', '$1 ' . $target_class . ' ', $tag);
        } else {
            if (strpos($tag, '<img') !== false) {
                $tag = str_replace('<img', '<img class="' . trim($target_class) . '" ', $tag);
            } elseif (strpos($tag, '<iframe') !== false) {
                global $TwoSettings;
                $two_delay_iframe_lazyload = $TwoSettings->get_settings('two_delay_iframe_lazyload');

                if ($two_delay_iframe_lazyload == 'on') {
                    $tag = str_replace('<iframe', '<iframe class="' . trim($target_class) . '_delay" ', $tag);
                } else {
                    $tag = str_replace('<iframe', '<iframe class="' . trim($target_class) . '" ', $tag);
                }
            } elseif (strpos($tag, '<video') !== false) {
                $tag = str_replace('<video', '<video class="' . trim($target_class) . '" ', $tag);
            }
        }

        return $tag;
    }

    public function get_default_lazyload_placeholder($imgopt_w, $imgopt_h)
    {
        return OptimizerUtils::SVG_DATA . $imgopt_w . '%20' . $imgopt_h . '%22%3E%3C/svg%3E';
    }

    public function get_smart_lazy_load_data()
    {
        return $this->smart_lazy_load_data;
    }

    private function smart_lazy_load_data($flag)
    {
        $this->smart_lazy_load_data[$flag] = true;
    }

    /**
     * Add missing attachment id-s to img tags and run WP function to add srcset and sizes attributes
     *
     * @param $content HTML content of the page
     *
     * @return string html page
     *
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public static function add_attachment_id_to_img($content)
    {
        $document = new \DiDom\Document($content);
        $images = $document->find('img:not([srcset]):not([class*="wp-image-"])');
        $image_urls = [];
        $dir = wp_get_upload_dir();

        // search all images in database with one query
        foreach ($images as $i => $image) {
            $imgUrl = $image->attr('src');
            $url = isset($imgUrl) ? $image->attr('src') : $image->attr('data-src');

            if (0 === strpos($url, $dir['baseurl'])) {
                $url = substr($url, strlen($dir[ 'baseurl' ] . '/'));
                // If the URL is auto-generated thumbnail, remove the sizes and get the URL of the original image
                $url = preg_replace('/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $url);
                $image_urls[ $i ] = $url;
            }
        }
        global $wpdb;
        $sql = $wpdb->prepare(
            "SELECT meta_value, post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value IN (%s)",
            implode(',', $image_urls)
        );
        $attach_ids = $wpdb->get_results($sql, OBJECT_K); // phpcs:ignore

        // add id to img-s w/o id class
        foreach ($images as $i => $image) {
            $imgUrl = $image->attr('src');
            $url = isset($imgUrl) ? $image->attr('src') : $image->attr('data-src');

            if (0 === strpos($url, $dir['baseurl'])) {
                $url_original = substr($url, strlen($dir[ 'baseurl' ] . '/'));
                // If the URL is auto-generated thumbnail, remove the sizes and get the URL of the original image
                $url = preg_replace('/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $url_original);

                if (isset($attach_ids[ $url ])) {
                    $image->attr('class', 'wp-image-' . $attach_ids[ $url ]->post_id);

                    if (preg_match_all('#<img[^>]*src=[^>]*' . $url_original . '[^>]*>#Usmi', $content, $matches)) {
                        foreach ($matches[0] as $tag) {
                            $to_replace[ $tag ] = self::inject_classes_in_tag($tag, 'wp-image-' . $attach_ids[ $url ]->post_id);
                        }
                        $content = str_replace(array_keys($to_replace), array_values($to_replace), $content);
                    }
                }
            }
        }

        // return html with added srcset and sizes attributes
        if (function_exists('wp_filter_content_tags')) {
            // Since WP 5.5.0
            return wp_filter_content_tags($content);
        } elseif (function_exists('wp_make_content_images_responsive')) {
            // Since WP 4.4.0. Deprecated in WP 5.5.0
            return wp_make_content_images_responsive($content);
        }
    }

    /**
     * Replace img tags with picture tags.
     * Thanks to Grégory Viguier for inspiration and some code fragments.
     *
     * @param $content HTML content of the page
     *
     * @return string html page
     */
    public static function replace_img_with_picture($content)
    {
        $html_no_picture_tags = self::remove_picture_tags($content);
        $images = self::get_images($html_no_picture_tags);

        if (! $images) {
            return $content;
        }

        foreach ($images as $image) {
            $tag = self::build_picture_tag($image);
            $content = str_replace($image['tag'], $tag, $content);
        }

        return $content;
    }

    private static function remove_picture_tags($html)
    {
        $replace = preg_replace('#<picture[^>]*>.*?<\/picture\s*>#mis', '', $html);

        if (null === $replace) {
            return $html;
        }

        return $replace;
    }

    protected static function get_images($content)
    {
        // Remove comments.
        $content = preg_replace('/<!--(.*)-->/Uis', '', $content);

        if (! preg_match_all('/<img\s.*>/isU', $content, $matches)) {
            return [];
        }

        $images = array_map([ 'self', 'process_image' ], $matches[0]);
        $images = array_filter($images);

        if (! $images || ! is_array($images)) {
            return [];
        }

        foreach ($images as $i => $image) {
            if (empty($image['src']['webp_exists']) || empty($image['src']['webp_url'])) {
                unset($images[ $i ]);
                continue;
            }

            unset($images[ $i ]['src']['webp_path'], $images[ $i ]['src']['webp_exists']);

            if (empty($image['srcset']) || ! is_array($image['srcset'])) {
                unset($images[ $i ]['srcset']);
                continue;
            }

            foreach ($image['srcset'] as $j => $srcset) {
                if (! is_array($srcset)) {
                    continue;
                }

                if (empty($srcset['webp_exists']) || empty($srcset['webp_url'])) {
                    unset($images[ $i ]['srcset'][ $j ]['webp_url']);
                }

                unset($images[ $i ]['srcset'][ $j ]['webp_path'], $images[ $i ]['srcset'][ $j ]['webp_exists']);
            }
        }

        return $images;
    }

    protected static function process_image($image)
    {
        $atts_pattern = '/(?<name>[^\s"\']+)\s*=\s*(["\'])\s*(?<value>.*?)\s*\2/s';

        if (! preg_match_all($atts_pattern, $image, $tmp_attributes, PREG_SET_ORDER)) {
            // No attributes?
            return false;
        }

        $attributes = [];

        foreach ($tmp_attributes as $attribute) {
            $attributes[ $attribute['name'] ] = $attribute['value'];
        }

        if (! empty($attributes['class']) && strpos($attributes['class'], 'two-no-webp') !== false) {
            return false;
        }

        // Deal with the src attribute.
        $src_source = false;

        foreach ([ 'data-lazy-src', 'data-src', 'src' ] as $src_attr) {
            if (! empty($attributes[ $src_attr ])) {
                $src_source = $src_attr;
                break;
            }
        }

        if (! $src_source) {
            // No src attribute.
            return false;
        }

        $extensions = [
            'jpg|jpeg|jpe' => 'image/jpeg',
            'png' => 'image/png',
            // phpcs:ignore Squiz.PHP.CommentedOutCode.Found
            //'gif'          => 'image/gif',
        ];
        $extensions = array_keys($extensions);
        $extensions = implode('|', $extensions);

        if (! preg_match('@^(?<src>(?:(?:https?:)?//|/).+\.(?<extension>' . $extensions . '))(?<query>\?.*)?$@i', $attributes[ $src_source ], $src)) {
            // Not a supported image format.
            return false;
        }

        $webp_url = $src['src'] . '.webp';
        $webp_path = self::url_to_path($webp_url);
        $webp_url .= ! empty($src['query']) ? $src['query'] : '';

        $data = [
            'tag' => $image,
            'attributes' => $attributes,
            'src_attribute' => $src_source,
            'src' => [
                'url' => $attributes[ $src_source ],
                'webp_url' => $webp_url,
                'webp_path' => $webp_path,
                'webp_exists' => $webp_path && @file_exists($webp_path),
            ],
            'srcset_attribute' => false,
            'srcset' => [],
        ];

        // Deal with the srcset attribute.
        $srcset_source = false;

        foreach ([ 'data-lazy-srcset', 'data-srcset', 'srcset' ] as $srcset_attr) {
            if (! empty($attributes[ $srcset_attr ])) {
                $srcset_source = $srcset_attr;
                break;
            }
        }

        if ($srcset_source) {
            $data['srcset_attribute'] = $srcset_source;

            $srcset = explode(',', $attributes[ $srcset_source ]);

            foreach ($srcset as $srcs) {
                $srcs = preg_split('/\s+/', trim($srcs));

                if (count($srcs) > 2) {
                    // Not a good idea to have space characters in file name.
                    $descriptor = array_pop($srcs);
                    $srcs = [ implode(' ', $srcs), $descriptor ];
                }

                if (empty($srcs[1])) {
                    $srcs[1] = '1x';
                }

                if (! preg_match('@^(?<src>(?:https?:)?//.+\.(?<extension>' . $extensions . '))(?<query>\?.*)?$@i', $srcs[0], $src)) {
                    // Not a supported image format.
                    $data['srcset'][] = [
                        'url' => $srcs[0],
                        'descriptor' => $srcs[1],
                    ];
                    continue;
                }

                $webp_url = $src['src'] . '.webp';
                $webp_path = self::url_to_path($webp_url);
                $webp_url .= ! empty($src['query']) ? $src['query'] : '';

                $data['srcset'][] = [
                    'url' => $srcs[0],
                    'descriptor' => $srcs[1],
                    'webp_url' => $webp_url,
                    'webp_path' => $webp_path,
                    'webp_exists' => $webp_path && @file_exists($webp_path),
                ];
            }
        }

        if (! $data || ! is_array($data)) {
            return false;
        }

        if (! isset($data['tag'], $data['attributes'], $data['src_attribute'], $data['src'], $data['srcset_attribute'], $data['srcset'])) {
            return false;
        }

        return $data;
    }

    protected static function url_to_path($url)
    {
        /**
         * $url, $uploads_url, $root_url, and $cdn_url are passed through `set_url_scheme()` only to make sure `stripos()` doesn't fail over a stupid http/https difference.
         */
        $uploads = wp_upload_dir();
        $baseurl = trailingslashit($uploads['baseurl']) ?? '';
        $uploads_url = set_url_scheme($baseurl);
        $basedir = trailingslashit($uploads['basedir']) ?? '';
        $uploads_dir = wp_normalize_path($basedir);
        $root_url = set_url_scheme(home_url('/'));
        $root_dir = ABSPATH;
        $domain_url = wp_parse_url($root_url);

        if (! empty($domain_url['scheme']) && ! empty($domain_url['host'])) {
            $domain_url = $domain_url['scheme'] . '://' . $domain_url['host'] . '/';
        } else {
            $domain_url = false;
        }

        // Get the right URL format.
        if ($domain_url && strpos($url, '/') === 0) {
            // URL like `/path/to/image.jpg.webp`.
            $url = $domain_url . ltrim($url, '/');
        }

        $url = set_url_scheme($url);

        // Return the path.
        if (stripos($url, $uploads_url) === 0) {
            return str_ireplace($uploads_url, $uploads_dir, $url);
        }

        if (stripos($url, $root_url) === 0) {
            return str_ireplace($root_url, $root_dir, $url);
        }

        return false;
    }

    protected static function build_picture_tag($image)
    {
        $to_remove = [
            'alt' => '',
            'height' => '',
            'width' => '',
            'data-lazy-src' => '',
            'data-src' => '',
            'src' => '',
            'data-lazy-srcset' => '',
            'data-srcset' => '',
            'srcset' => '',
            'data-lazy-sizes' => '',
            'data-sizes' => '',
            'sizes' => '',
        ];

        $attributes = array_diff_key($image['attributes'], $to_remove);

        /*
         * Remove Gutenberg specific attributes from picture tag, leave them on img tag.
         */
        if (! empty($image['attributes']['class']) && strpos($image['attributes']['class'], 'wp-block-cover__image-background') !== false) {
            unset($attributes['style']);
            unset($attributes['class']);
            unset($attributes['data-object-fit']);
            unset($attributes['data-object-position']);
        }

        $output = '<picture' . self::build_attributes($attributes) . ">\n";
        $output .= self::build_source_tag($image);
        $output .= self::build_img_tag($image);
        $output .= "</picture>\n";

        return $output;
    }

    protected static function build_attributes($attributes)
    {
        if (! $attributes || ! is_array($attributes)) {
            return '';
        }

        $out = '';

        foreach ($attributes as $attribute => $value) {
            $out .= ' ' . $attribute . '="' . esc_attr($value) . '"';
        }

        return $out;
    }

    protected static function build_source_tag($image)
    {
        $srcset_source = ! empty($image['srcset_attribute']) ? $image['srcset_attribute'] : $image['src_attribute'] . 'set';
        $attributes = [
            'type' => 'image/webp',
            $srcset_source => [],
        ];

        if (! empty($image['srcset'])) {
            foreach ($image['srcset'] as $srcset) {
                if (empty($srcset['webp_url'])) {
                    continue;
                }

                $attributes[ $srcset_source ][] = $srcset['webp_url'] . ' ' . $srcset['descriptor'];
            }
        }

        if (empty($attributes[ $srcset_source ])) {
            $attributes[ $srcset_source ][] = $image['src']['webp_url'];
        }

        $attributes[ $srcset_source ] = implode(', ', $attributes[ $srcset_source ]);

        foreach ([ 'data-lazy-srcset', 'data-srcset', 'srcset' ] as $srcset_attr) {
            if (! empty($image['attributes'][ $srcset_attr ]) && $srcset_attr !== $srcset_source) {
                $attributes[ $srcset_attr ] = $image['attributes'][ $srcset_attr ];
            }
        }

        if ('srcset' !== $srcset_source && empty($attributes['srcset']) && ! empty($image['attributes']['src'])) {
            // Lazyload: the "src" attr should contain a placeholder (a data image or a blank.gif ).
            $attributes['srcset'] = $image['attributes']['src'];
        }

        foreach ([ 'data-lazy-sizes', 'data-sizes', 'sizes' ] as $sizes_attr) {
            if (! empty($image['attributes'][ $sizes_attr ])) {
                $attributes[ $sizes_attr ] = $image['attributes'][ $sizes_attr ];
            }
        }

        return '<source' . self::build_attributes($attributes) . "/>\n";
    }

    protected static function build_img_tag($image)
    {
        /*
         * Gutenberg fix.
         * Check for the 'wp-block-cover__image-background' class on the original image, and leave that class and style attributes if found.
         */
        if (! empty($image['attributes']['class']) && strpos($image['attributes']['class'], 'wp-block-cover__image-background') !== false) {
            $to_remove = [
                'id' => '',
                'title' => '',
            ];

            $attributes = array_diff_key($image['attributes'], $to_remove);
        } else {
            $to_remove = [
                'class' => '',
                'id' => '',
                'style' => '',
                'title' => '',
            ];

            $attributes = array_diff_key($image['attributes'], $to_remove);
        }

        return '<img' . self::build_attributes($attributes) . "/>\n";
    }
}
