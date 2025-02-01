<?php

namespace TenWebOptimizer;

class OptimizerStyles extends OptimizerBase
{
    const TWO_DELAYED_CSS_ATTRIBUTE = 'data-twodelayedcss';

    const ASSETS_REGEX = '/url\s*\(\s*(?!["\']?data:)(?![\'|\"]?[\#|\%|])([^)]+)\s*\)([^;},\s]*)/i';

    /**
     * Font-face regex-fu from HamZa at: https://stackoverflow.com/a/21395083
     * ~
     *
     * @font-face\s* # Match @font-face and some spaces
     * (             # Start group 1
     * \{            # Match {
     * (?:           # A non-capturing group
     * [^{}]+        # Match anything except {} one or more times
     * |             # Or
     * (?1)          # Recurse/rerun the expression of group 1
     * )*            # Repeat 0 or more times
     * \}            # Match }
     * )             # End group 1
     * ~xs';
     */
    const FONT_FACE_REGEX = '~@font-face\s*(\{(?:[^{}]+|(?1))*\})~xsi'; // added `i` flag for case-insensitivity.

    const IMPORT_URL_REGEX = '/@import.*url.*\(.*[\'|"](.*)[\'|"].*\)/Umsi';

    private $css = [];

    private $csscode = [];

    private $url = [];

    private $restofcontent = '';

    private $datauris = false;

    private $hashmap = [];

    private $alreadyminified = false;

    private $aggregate = false;

    private $inline = false;

    private $defer = false;

    private $defer_inline = false;

    private $whitelist = '';

    private $cssinlinesize = '';

    private $cssremovables = [];

    private $cssdisables = [];

    private $include_inline = false;

    private $inject_min_late = '';

    private $dontmove = [];

    private $options = [];

    private $minify_css = true;

    private $current_url = null;

    private $url_data = null;

    private $async_type = 'stylesheet';

    private $font_swap = false;

    private $two_load_fonts_via_webfont = false;

    public $webFont_list = [];

    public $critical = null;

    // public $cdn_url; // Used all over the place implicitly, so will have to be either public or protected :/ .
    // Reads the page and collects style tags.
    /**
     * @var false|mixed|void
     */
    private $criticalCss;

    /**
     * @var array
     */
    private $hashes;

    /**
     * @var OptimizerCacheStructure
     */
    private $cacheStructure;

    /**
     * OptimizerStyles constructor.
     *
     * @param string                  $content
     * @param OptimizerCacheStructure $cacheStructure
     */
    public $two_async_css_arr = [];

    public $two_critical_connection_data = [
        'critical_css' => false,
        'critical_fonts' => false
    ];

    public $critical_fonts_arr = [];

    public $use_uncritical = false;

    private $TwoSettings;

    public function __construct($content, $cacheStructure, $critical = null)
    {
        global $TwoSettings;
        $this->TwoSettings = $TwoSettings;
        $this->critical = $critical;
        parent::__construct($content);
        $this->cacheStructure = $cacheStructure;
    }

    public function read($options)
    {
        $excludeCSS = $options['css_exclude'];

        if ('' !== $excludeCSS) {
            $this->dontmove = array_filter(array_map('trim', explode(',', $excludeCSS)));
        } else {
            $this->dontmove = [];
        }
        // forcefully exclude CSS with data-noptimize attrib.
        $this->dontmove[] = 'data-noptimize';
        $this->dontmove[] = 'two_critical_bg';

        if ($this->critical->critical_enabled && $this->critical->use_uncritical && $this->critical->status == 'success' && isset($this->critical->uncritical_css)) {
            $this->use_uncritical = true;

            return;
        }
        $this->replaceOptions($options);
        $this->current_url = OptimizerUtils::get_page_url();
        $this->url_data = OptimizerUtils::remove_domain_part($this->current_url);
        $this->font_swap = empty($this->TwoSettings->get_settings('two_async_font')) ? false : true;
        $this->two_load_fonts_via_webfont = empty($this->TwoSettings->get_settings('two_load_fonts_via_webfont')) ? false : true;
        $two_disable_css = $this->TwoSettings->get_settings('two_disable_css');
        $two_disable_css_page = $this->TwoSettings->get_settings('two_disable_page');

        if (is_array($two_disable_css_page) && (isset($two_disable_css_page[$this->url_data]) || isset($two_disable_css_page[$this->current_url]))) {
            if (isset($two_disable_css) && !empty($two_disable_css)) {
                if (isset($two_disable_css_page[$this->url_data])) {
                    $two_disable_css .= ',' . $two_disable_css_page[$this->url_data];
                }

                if (isset($two_disable_css_page[$this->current_url])) {
                    $two_disable_css .= ',' . $two_disable_css_page[$this->current_url];
                }
            } else {
                if (isset($two_disable_css_page[$this->url_data])) {
                    $two_disable_css = $two_disable_css_page[$this->url_data];
                }

                if (isset($two_disable_css_page[$this->current_url])) {
                    if (isset($two_disable_css) && !empty($two_disable_css)) {
                        $two_disable_css .= ',' . $two_disable_css_page[$this->current_url];
                    } else {
                        $two_disable_css .= $two_disable_css_page[$this->current_url];
                    }
                }
            }
        }

        $two_disable_css = explode(',', $two_disable_css);
        $this->cssdisables = array_filter($two_disable_css);

        $this->cssinlinesize = 256;
        // filter to "late inject minified CSS", default to true for now (it is faster).
        $this->inject_min_late = true;

        // Determine whether we're doing CSS-files aggregation or not.
        if (isset($options['aggregate'])) {
            $this->aggregate = $options['aggregate'];
        }

        // Returning true for "dontaggregate" turns off aggregation.
        // include inline?
        if ($options['include_inline'] && $this->aggregate) {
            $this->include_inline = true;
        }

        // Should we defer css?
        // value: true / false.
        $this->defer = $options['defer'];
        // Should we inline while deferring?
        // value: inlined CSS.
        $this->defer_inline = $options['defer_inline'];
        // Should we inline?
        // value: true / false.
        $this->inline = $options['inline'];

        $this->minify_css = $options['minify_css'];

        // noptimize me.
        $this->content = $this->hide_noptimize($this->content);
        // Exclude (no)script, as those may contain CSS which should be left as is.
        $this->content = $this->replace_contents_with_marker_if_exists('SCRIPT', '<script', '#<(?:no)?script.*?<\/(?:no)?script>#is', $this->content);
        // Save IE hacks.
        $this->content = $this->hide_iehacks($this->content);
        // Hide HTML comments.
        $this->content = $this->hide_comments($this->content);
        // Get <style> and <link>.
        $current_media = 'all';

        if (preg_match_all('#(<style[^>]*>.*</style>)|(<link[^>]*stylesheet[^>]*>)#Usmi', $this->content, $matches)) {
            foreach ($matches[0] as $tag) {
                if ($this->isremovable($tag, $this->cssremovables)) {
                    $this->content = str_replace($tag, '', $this->content);
                    $this->cacheStructure->addToTagsToReplace($tag, '');
                }

                if ($this->is_disable($tag, $this->cssdisables)) {
                    $this->content = str_replace($tag, '', $this->content);
                    $this->cacheStructure->addToTagsToReplace($tag, '');
                } elseif ($this->ismovable($tag)) {
                    // Get the media.
                    $replace_tag = '';

                    if (false !== strpos($tag, 'media=')) {
                        preg_match('#media=(?:"|\')([^>]*)(?:"|\')#Ui', $tag, $medias);
                        $medias = explode(',', $medias[1]);
                        $media = [];

                        foreach ($medias as $elem) {
                            if (empty($elem)) {
                                $elem = 'all';

                                if ($this->options['async_all']) {
                                    $elem = 'all_none';
                                    $current_media = 'all';
                                }
                            }

                            if ($this->is_async($tag)) {
                                $current_media = $elem;
                                $elem = $elem . '_none';
                            }
                            $media[] = $elem;
                        }
                    } else {
                        // No media specified - applies to all.
                        $media = ['all'];

                        if ($this->options['async_all']) {
                            $media = ['all_none'];
                        }
                    }

                    if (preg_match('#<link.*href=("|\')(.*)("|\')#Usmi', $tag, $source)) {
                        // <link>.
                        $url = current(explode('?', $source[2], 2));
                        $current_url = $source[2];
                        $path = $this->getpath($url);

                        if (false !== $path && preg_match('#\.css$#', $path)) {
                            // Good link.
                            $this->css[md5($path)] = [$media, $path];
                        } else {
                            $new_tag = '';

                            if (strpos($source[2], 'fonts.googleapis')) {
                                $font_family = OptimizerUtils::get_url_query($source[2], 'family');

                                if (strpos($source[2], 'http') === false && substr($source[2], 0, 2) == '//') {
                                    $source[2] = 'https:' . $source[2];
                                }

                                if (!$this->is_async($tag) && $this->two_load_fonts_via_webfont && $font_family) {
                                    $this->content = str_replace($tag, '', $this->content);
                                    $this->cacheStructure->addToTagsToReplace($tag, '');
                                    $font_family = explode('|', $font_family);

                                    foreach ($font_family as $font) {
                                        $this->webFont_list[] = $font;
                                    }
                                } elseif ($this->font_swap) {
                                    $google_fonts_src = OptimizerUtils::replace_google_font_url($source[2]);
                                    $current_url = $google_fonts_src;
                                    $new_tag = str_replace($source[2], $google_fonts_src, $tag);
                                    $this->content = str_replace($tag, $new_tag, $this->content);
                                    $this->cacheStructure->addToTagsToReplace($tag, $new_tag);
                                    $tag = $new_tag;
                                }
                            } else {
                                $new_tag = $tag;
                            }

                            if ($new_tag !== '' && $new_tag !== $tag && !strpos($source[2], 'fonts.googleapis')) {
                                if ($this->is_async($tag)) {
                                    $this->two_async_css_arr[] = [
                                        'url' => $current_url,
                                        'media' => $current_media,
                                        'uid' => ''
                                    ];
                                    $new_tag = '';
                                }
                                $this->content = str_replace($tag, $new_tag, $this->content);
                                $this->cacheStructure->addToTagsToReplace($tag, $new_tag);
                            }

                            // Link is dynamic (.php etc).
                            if ($this->is_async($tag)) {
                                $this->two_async_css_arr[] = [
                                    'url' => $current_url,
                                    'media' => $current_media,
                                    'uid' => ''
                                ];
                                $replace_tag = '';
                            } else {
                                $tag = '';
                            }
                        }
                    } else {
                        //optimize inline styles
                        list($originalCode, $code) = $this->optimizeInlineStyle($tag);

                        if ($this->include_inline) {
                            $this->css[md5($code)] = [$media, 'INLINE;' . $code];
                        } else {
                            //here we change inline styles code inside <style> tag to optimized one
                            $id_empty_tag = preg_replace('/\s+/', '', $originalCode);

                            if (!empty($id_empty_tag)) {
                                $tag = $originalCode;
                                $replace_tag = $code;
                            }
                        }
                    }
                    // Remove the original style tag.
                    $this->content = str_replace($tag, $replace_tag, $this->content, $changesMade);
                    $this->cacheStructure->addToTagsToReplace($tag, $replace_tag);
                } else {
                    if (preg_match('#<link.*href=("|\')(.*)("|\')#Usmi', $tag, $source)) {
                        $exploded_url = explode('?', $source[2], 2);
                        $url = $exploded_url[0];
                        $path = $this->getpath($url);
                        $new_tag = $tag;
                        // Excluded CSS, minify that file:
                        // -> if aggregate is on and exclude minify is on
                        // -> if aggregate is off and the file is not in dontmove.

                        if ($this->is_async($tag)) {
                            $this->two_async_css_arr[] = [
                                'url' => $source[2],
                                'media' => 'all',
                                'uid' => ''
                            ];
                            $new_tag = '';
                        }

                        if ($path && $this->minify_css) {
                            $consider_minified_array = false;

                            if ((false === $this->aggregate && str_replace($this->dontmove, '', $path) === $path) || (true === $this->aggregate && (false === $consider_minified_array || str_replace($consider_minified_array, '', $path) === $path))) {
                                $minified_url = $this->minify_single($path);

                                if (!empty($minified_url)) {
                                    // Replace orig URL with cached minified URL.
                                    $new_tag = str_replace($url, $minified_url, $tag);
                                }
                            }
                        }

                        // And replace
                        if ($new_tag !== '' && $new_tag !== $tag) {
                            $this->content = str_replace($tag, $new_tag, $this->content);
                            $this->cacheStructure->addToTagsToReplace($tag, $new_tag);
                        }
                    } else {
                        //optimize inline styles
                        list($originalCode, $code) = $this->optimizeInlineStyle($tag);

                        if ($code !== '' && $code !== $originalCode) {
                            $this->content = str_replace($originalCode, $code, $this->content);
                            $this->cacheStructure->addToTagsToReplace($originalCode, $code);
                        }
                    }
                }
            }

            return $this->content;
        }

        // Really, no styles?
        return false;
    }

    /**
     * Run CSS optimization for code inside style tag and returns array of original and optimized code
     *
     * @return array [$originalCode, $optimizedCode]
     */
    private function optimizeInlineStyle($tag)
    {
        $cssMinifier = new OptimizerCSSMin();
        // Inline css in style tags can be wrapped in comment tags, so restore comments.
        $tag = $this->restore_comments($tag);
        preg_match('#<style.*>(.*)</style>#Usmi', $tag, $code);

        if (empty($code)) {
            return ['', ''];
        }
        $originalCode = $code[1];
        // And re-hide them to be able to to the removal based on tag.
        $tag = $this->hide_comments($tag);
        $code = preg_replace('#^.*<!\[CDATA\[(?:\s*\*/)?(.*)(?://|/\*)\s*?\]\]>.*$#sm', '$1', $code[1]);
        //run optimizations without minifying
        $code = $cssMinifier->run($code, false);

        return [$originalCode, $code];
    }

    private function is_async($tag)
    {
        if ($this->options['disable_async']) {
            return false;
        }

        if (!$this->ismovable($tag)) {
            return false;
        }

        if (is_array($this->options) && isset($this->options['async_all']) && $this->options['async_all']) {
            return true;
        }
        $two_async_css_list = $this->TwoSettings->get_settings('two_async_css');
        $two_async_page = $this->TwoSettings->get_settings('two_async_page');

        if (is_array($two_async_page) && (isset($two_async_page[$this->url_data]) || isset($two_async_page[$this->current_url]))) {
            if (isset($two_async_css_list) && !empty($two_async_css_list)) {
                if (isset($two_async_page[$this->url_data])) {
                    $two_async_css_list .= ',' . $two_async_page[$this->url_data];
                }

                if (isset($two_async_page[$this->current_url])) {
                    $two_async_css_list .= ',' . $two_async_page[$this->current_url];
                }
            } else {
                if (isset($two_async_page[$this->url_data])) {
                    $two_async_css_list = $two_async_page[$this->url_data];
                }

                if (isset($two_async_page[$this->current_url])) {
                    if (!isset($two_async_css_list) && !empty($two_async_css_list)) {
                        $two_async_css_list .= ',' . $two_async_page[$this->current_url];
                    } else {
                        $two_async_css_list = $two_async_page[$this->current_url];
                    }
                }
            }
        }
        $two_async_css = [];

        if (isset($two_async_css_list) && $two_async_css_list != false) {
            $two_async_css = explode(',', str_replace(' ', '', $two_async_css_list));
        }
        $flag = false;

        foreach ($two_async_css as $val) {
            if ($flag) {
                break;
            }

            if (!empty($val)) {
                $pos = strpos($tag, $val);

                if ($pos !== false) {
                    $flag = true;
                }
            }
        }

        if ($flag) {
            return true;
        }

        return false;
    }

    /**
     * Given an array of key/value pairs to replace in $string,
     * it does so by replacing the longest-matching strings first.
     *
     * @param string $string
     * @param array  $replacements
     *
     * @return string
     */
    protected static function replace_longest_matches_first($string, $replacements = [])
    {
        if (!empty($replacements)) {
            // Sort the replacements array by key length in desc order (so that the longest strings are replaced first).
            $keys = array_map('strlen', array_keys($replacements));
            array_multisort($keys, SORT_DESC, $replacements);
            $string = str_replace(array_keys($replacements), array_values($replacements), $string);
        }

        return $string;
    }

    public function replace_urls($code = '')
    {
        $replacements = [];
        $code = self::replace_longest_matches_first($code, $replacements);

        return $code;
    }

    public function hide_fontface_and_maybe_cdn($code)
    {
        // Proceed only if @font-face declarations exist within $code.
        preg_match_all(self::FONT_FACE_REGEX, $code, $fontfaces);

        if (isset($fontfaces[0])) {
            // Check if we need to cdn fonts or not.
            $do_font_cdn = false;

            foreach ($fontfaces[0] as $full_match) {
                // Keep original match so we can search/replace it.
                $match_search = $full_match;

                // Do font cdn if needed.
                if ($do_font_cdn) {
                    $full_match = $this->replace_urls($full_match);
                }
                // Replace declaration with its base64 encoded string.
                $replacement = self::build_marker('FONTFACE', $full_match);
                $code = str_replace($match_search, $replacement, $code);
            }
        }

        return $code;
    }

    /**
     * Restores original @font-face declarations that have been "hidden"
     * using `hide_fontface_and_maybe_cdn()`.
     *
     * @param string $code
     *
     * @return string
     */
    public function restore_fontface($code)
    {
        return $this->restore_marked_content('FONTFACE', $code);
    }

    // Re-write (and/or inline) referenced assets.
    public function rewrite_assets($code, $hashes)
    {
        // Handle @font-face rules by hiding and processing them separately.
        $code = $this->hide_fontface_and_maybe_cdn($code);
        // Re-write (and/or inline) URLs to point them to the CDN host.
        $url_src_matches = [];
        $imgreplace = [];
        // Matches and captures anything specified within the literal `url()` and excludes those containing data: URIs.
        preg_match_all(self::ASSETS_REGEX, $code, $url_src_matches);

        $code = self::replace_longest_matches_first($code, $imgreplace);
        // Replace back font-face markers with actual font-face declarations.
        $code = $this->restore_fontface($code);

        return $code;
    }

    // Joins and optimizes CSS.
    public function optimize()
    {
        foreach ($this->css as $styleHash => $group) {
            list($media, $css) = $group;
            $cssPath = '';

            if (preg_match('#^INLINE;#', $css)) {
                // <style>.
                $css = preg_replace('#^INLINE;#', '', $css);
                $css = self::fixurls(ABSPATH . 'index.php', $css); // ABSPATH already contains a trailing slash.
                $this->hashes[] = $styleHash;
            } else {
                // <link>
                if (false !== $css && file_exists($css) && is_readable($css)) {
                    $cssPath = $css;
                    $css = self::fixurls($cssPath, file_get_contents($cssPath)); // phpcs:ignore
                    $css = preg_replace('/\x{EF}\x{BB}\x{BF}/', '', $css);

                    if ($this->can_inject_late($cssPath, $css)) {
                        $css = self::build_injectlater_marker($cssPath, md5($css));
                    }
                    $this->hashes[] = $styleHash;
                } else {
                    // Couldn't read CSS. Maybe getpath isn't working?
                    $css = '';
                }
            }

            foreach ($media as $elem) {
                if (!empty($css)) {
                    if (!empty($elem)) {
                        $css_media = $elem;
                        $pos = strpos($css_media, '_none');

                        if ($pos) {
                            $css_media = str_replace('_none', '', $css_media);
                        } else {
                            $elem = 'all';
                        }

                        if ($css_media != 'all') {
                            $css = '@media ' . $css_media . '{ ' . $css . ' }';
                        }
                    }

                    if (!isset($this->csscode[$elem])) {
                        $this->csscode[$elem] = '';
                    }
                    $this->csscode[$elem] .= "\n\n/*FILESTART  " . ($cssPath ? $cssPath : '') . " */\n" . $css;
                }
            }
        }
        // Check for duplicate code.
        $md5list = [];
        $tmpcss = $this->csscode;

        foreach ($tmpcss as $media => $code) {
            $md5sum = md5($code);
            $medianame = $media;

            foreach ($md5list as $med => $sum) {
                // If same code.
                if ($sum === $md5sum) {
                    // Add the merged code.
                    $medianame = $med . ', ' . $media;
                    $this->csscode[$medianame] = $code;
                    $md5list[$medianame] = $md5list[$med];
                    unset($this->csscode[$med], $this->csscode[$media], $md5list[$med]);
                }
            }
            $md5list[$medianame] = $md5sum;
        }
        unset($tmpcss);

        // Manage @imports, while is for recursive import management.
        foreach ($this->csscode as &$thiscss) {
            // Flag to trigger import reconstitution and var to hold external imports.
            $fiximports = false;
            $external_imports = '';
            // remove comments to avoid importing commented-out imports.
            $thiscss_nocomments = preg_replace('#/\*.*\*/#Us', '', $thiscss);

            while (preg_match_all('#@import +(?:url)?(?:(?:\((["\']?)(?:[^"\')]+)\1\)|(["\'])(?:[^"\']+)\2)(?:[^,;"\']+(?:,[^,;"\']+)*)?)(?:;)#mi', $thiscss_nocomments, $matches)) {
                foreach ($matches[0] as $import) {
                    if ($this->isremovable($import, $this->cssremovables)) {
                        $thiscss = str_replace($import, '', $thiscss);
                        $import_ok = true;
                    } else {
                        $url = trim(preg_replace('#^.*((?:https?:|ftp:)?//.*\.css).*$#', '$1', trim($import)), " \t\n\r\0\x0B\"'");
                        $path = $this->getpath($url);
                        $import_ok = false;

                        if (file_exists($path) && is_readable($path)) {
                            $code = addcslashes(self::fixurls($path, file_get_contents($path)), '\\');
                            $code = preg_replace('/\x{EF}\x{BB}\x{BF}/', '', $code);
                            $tmpstyle = $code;

                            if (!empty($tmpstyle)) {
                                $code = $tmpstyle;
                                $this->alreadyminified = true;
                            } elseif ($this->can_inject_late($path, $code)) {
                                $code = self::build_injectlater_marker($path, md5($code));
                            }

                            if (!empty($code)) {
                                $tmp_thiscss = preg_replace('#(/\*FILESTART\*/.*)' . preg_quote($import, '#') . '#Us', '/*FILESTART2*/' . $code . '$1', $thiscss, -1, $replaceCount);

                                if (!empty($tmp_thiscss) && !empty($replaceCount)) {
                                    $thiscss = $tmp_thiscss;
                                    $import_ok = true;
                                    unset($tmp_thiscss);
                                }
                            }
                            unset($code);
                        }
                    }

                    if (!$import_ok) {
                        // External imports and general fall-back.
                        $external_imports .= $import;
                        $thiscss = str_replace($import, '', $thiscss);
                        $fiximports = true;
                    }
                }
                $thiscss = preg_replace('#/\*FILESTART\*/#', '', $thiscss);
                $thiscss = preg_replace('#/\*FILESTART2\*/#', '/*FILESTART*/', $thiscss);
                // and update $thiscss_nocomments before going into next iteration in while loop.
                $thiscss_nocomments = preg_replace('#/\*.*\*/#Us', '', $thiscss);
            }

            unset($thiscss_nocomments);

            // Add external imports to top of aggregated CSS.
            if ($fiximports) {
                $thiscss = $external_imports . $thiscss;
            }
        }
        unset($thiscss);

        // $this->csscode has all the uncompressed code now.
        foreach ($this->csscode as &$code) {
            $hash = md5($code);
            // Rewrite and/or inline referenced assets.
            $code = $this->rewrite_assets($code, $this->hashes);
            // Load Google fonts via webfont
            $code = $this->get_replace_GoogleFonts($code);
            // Minify.
            $code = $this->run_minifier_on($code);
            // Bring back INJECTLATER stuff.
            $code = $this->inject_minified($code);

            // Filter results.
            $tmp_code = $code;

            if (!empty($tmp_code)) {
                $code = $tmp_code;
                unset($tmp_code);
            }
            $this->hashmap[md5($code)] = $hash;
        }
        unset($code);

        return true;
    }

    /*replace google fonts to empty string for WebFont*/
    public function get_replace_GoogleFonts($code)
    {
        preg_match_all(self::IMPORT_URL_REGEX, $code, $matches, PREG_SET_ORDER, 0);

        if (is_array($matches)) {
            foreach ($matches as $font_el) {
                if (isset($font_el[0]) && isset($font_el[1])) {
                    if (filter_var($font_el[1], FILTER_VALIDATE_URL) != false) {
                        $url = $font_el[1];
                        $font_family = OptimizerUtils::get_url_query($url, 'family');

                        if ($font_family) {
                            $code = str_replace($font_el[0], '', $code);
                            $font_family = explode('|', $font_family);

                            foreach ($font_family as $font) {
                                $this->webFont_list[] = $font;
                            }
                        }
                    }
                }
            }
        }

        return $code;
    }

    public function run_minifier_on($code)
    {
        if (!$this->alreadyminified) {
            $do_minify = true;

            if ($do_minify) {
                $cssmin = new OptimizerCSSMin();
                $tmp_code = trim($cssmin->run($code, $this->minify_css));

                if (!empty($tmp_code)) {
                    $code = $tmp_code;
                    unset($tmp_code);
                }
            }
        }

        return $code;
    }

    // Caches the CSS in uncompressed, deflated and gzipped form.
    public function cache()
    {
        // CSS cache.
        foreach ($this->csscode as $media => $code) {
            $cache = new OptimizerCache(null, 'css', $media);
            $cache->cache($code, 'text/css');
            $this->url[$media] = TWO_CACHE_URL . $cache->getname();
        }
    }

    // Returns the content.
    public function getcontent()
    {
        if (!empty($this->restofcontent)) {
            $this->content .= $this->restofcontent;
            $this->restofcontent = '';
        }
        // Inject the new stylesheets.
        $replaceTag = ['<head', 'after_tag'];

        if ($this->inline) {
            foreach ($this->csscode as $media => $code) {
                $this->content = OptimizerUtils::inject_in_html($this->content, '<style type="text/css" media="' . $media . '">' . $code . '</style>', $replaceTag);
                $this->cacheStructure->addToTagsToAdd('<style type="text/css" media="' . $media . '">' . $code . '</style>', $replaceTag);
            }
        } else {
            foreach ($this->url as $media => $url) {
                $url = $this->url_replace_cdn($url);
                $load_none = '';
                $rel = 'stylesheet';

                $css_href = 'href';

                if ($this->critical->uncritical_load_type === 'on_interaction' && $this->critical->critical_enabled) {
                    $css_href = self::TWO_DELAYED_CSS_ATTRIBUTE;
                }
                $two_new_tag = '<link type="text/css" media="' . $media . '" ' . $css_href . '="' . $url . '" rel="' . $rel . '" ' . $load_none . ' />';

                $pos = strpos($media, '_none');

                if ($pos) {
                    $data_media = str_replace('_none', '', $media);
                    $load_none = 'data-two_media="' . $data_media . '" onload="if(media!=\'all\')media=this.getAttribute(\'data-two_media\');"';
                    $media = 'none';
                    $rel = 'stylesheet';
                    $two_new_tag = '';
                    $this->two_async_css_arr[] = [
                        'url' => $url,
                        'media' => $data_media,
                        'uid' => ''
                    ];
                }
                $this->content = OptimizerUtils::inject_in_html(
                    $this->content,
                    $two_new_tag,
                    $replaceTag
                );
                $this->cacheStructure->addToTagsToAdd(
                    $two_new_tag,
                    $replaceTag
                );
            }
        }
        $this->content = OptimizerUtils::injectCriticalBg($this->content, $this->critical, $this->cacheStructure);

        if ($this->critical->critical_enabled || $this->critical->critical_font_enabled) {
            $this->content = $this->injectCriticalCss();
        }
        // restore comments.
        $this->content = $this->restore_comments($this->content);
        // restore IE hacks.
        $this->content = $this->restore_iehacks($this->content);
        // restore (no)script.
        $this->content = $this->restore_marked_content('SCRIPT', $this->content);
        // Restore noptimize.
        $this->content = $this->restore_noptimize($this->content);

        // Return the modified stylesheet.
        return $this->content;
    }

    public static function fixurls($file, $code, $asyncAllIsEnabled = false)
    {
        // Switch all imports to the url() syntax.
        $code = preg_replace('#@import ("|\')(.+?)\.css.*?("|\')#', '@import url("${2}.css")', $code);

        if (preg_match_all(self::ASSETS_REGEX, $code, $matches)) {
            $file = str_replace(WP_ROOT_DIR, '/', $file);
            $dir = dirname($file); // Like /themes/expound/css.
            /**
             * $dir should not contain backslashes, since it's used to replace
             * urls, but it can contain them when running on Windows because
             * fixurls() is sometimes called with `ABSPATH . 'index.php'`
             */
            $dir = str_replace('\\', '/', $dir);
            unset($file); // not used below at all.
            $replace = [];

            foreach ($matches[1] as $k => $url) {
                // Remove quotes.
                $old_url = $url;
                $url = trim($url, " \t\n\r\0\x0B\"'");
                $noQurl = trim($url, "\"'");

                if ($old_url !== $noQurl) {
                    $removedQuotes = true;
                } else {
                    $removedQuotes = false;
                }

                if ('' === $noQurl) {
                    continue;
                }
                $url = $noQurl;

                if (preg_match('#^(https?://|ftp://|data:)#i', $url)) {
                    // URL is protocol-relative, host-relative or something we don't touch.
                    continue;
                } else {
                    if (strpos($url, '//') === 0) {
                        $url_data = wp_parse_url($url);

                        if (is_array($url_data) && isset($url_data['host'])) {
                            if (strpos($url, '//' . $url_data['host']) == 0) {
                                $newurl = str_replace('//' . $url_data['host'], TWO_WP_ROOT_URL, $url);
                            } else {
                                $newurl = str_replace('//', TWO_WP_ROOT_URL, $url);
                            }
                        } else {
                            continue;
                        }
                    } elseif (strpos($url, '/') === 0) {
                        $newurl = TWO_WP_ROOT_URL . $url;
                    } else {
                        $newurl = str_replace(' ', '%20', TWO_WP_ROOT_URL . str_replace('//', '/', $dir . '/' . $url));
                    }
                    /**
                     * Hash the url + whatever was behind potentially for replacement
                     * We must do this, or different css classes referencing the same bg image (but
                     * different parts of it, say, in sprites and such) loose their stuff...
                     */
                    $hash = md5($url . $matches[2][$k]);
                    $code = str_replace($matches[0][$k], $hash, $code);

                    if ($removedQuotes) {
                        $replace[$hash] = "url('" . $newurl . "')" . $matches[2][$k];
                    } else {
                        $replace[$hash] = 'url(' . $newurl . ')' . $matches[2][$k];
                    }
                }
            }
            $code = self::replace_longest_matches_first($code, $replace);
        }

        return $code;
    }

    private function ismovable($tag)
    {
        if (!$this->aggregate) {
            return false;
        }

        if (!empty($this->whitelist)) {
            foreach ($this->whitelist as $match) {
                if (false !== strpos($tag, $match)) {
                    return true;
                }
            }

            // no match with whitelist.
            return false;
        } else {
            if (is_array($this->dontmove) && !empty($this->dontmove)) {
                foreach ($this->dontmove as $match) {
                    if (false !== strpos($tag, $match)) {
                        // Matched something.
                        return false;
                    }
                }
            }

            // If we're here it's safe to move.
            return true;
        }
    }

    private function can_inject_late($cssPath, $css)
    {
        $consider_minified_array = false;

        if (true !== $this->inject_min_late) {
            // late-inject turned off.
            return false;
        } elseif ((false === strpos($cssPath, 'min.css')) && (str_replace($consider_minified_array, '', $cssPath) === $cssPath)) {
            // file not minified based on filename & filter.
            return false;
        } elseif (false !== strpos($css, '@import')) {
            // can't late-inject files with imports as those need to be aggregated.
            return false;
        } elseif (preg_match('#background[^;}]*url\(#Ui', $css)) {
            // don't late-inject CSS with images if CDN is set OR if image inlining is on.
            return false;
        } else {
            // phew, all is safe, we can late-inject.
            return true;
        }
    }

    /**
     * Minifies (and cdn-replaces) a single local css file
     * and returns its (cached) url.
     *
     * @param string $filepath   filepath
     * @param bool   $cache_miss Optional. Force a cache miss. Default false.
     *
     * @return bool|string url pointing to the minified css file or false
     */
    public function minify_single($filepath, $cache_miss = false)
    {
        $contents = $this->prepare_minify_single($filepath);

        if (empty($contents)) {
            return false;
        }
        // Check cache.
        $name_prefix = 'minified_' . str_replace('.css', '', basename($filepath));
        $cache = new OptimizerCache(null, 'css', 'all', $name_prefix);
        // Fixurls...
        $contents = self::fixurls($filepath, $contents);
        // CDN-replace any referenced assets if needed...
        $contents = $this->replace_urls($contents);
        // Now minify...
        $cssmin = new OptimizerCSSMin();
        $contents = trim($cssmin->run($contents, $this->minify_css));
        // Store in cache.
        $contents = $this->get_replace_GoogleFonts($contents);
        $cache->cache($contents, 'text/css');
        $url = $this->build_minify_single_url($cache);

        return $url;
    }

    public function replaceOptions($options)
    {
        $this->options = $options;
    }

    private function is_disable($tag, $disables_css)
    {
        foreach ($disables_css as $match) {
            if (false !== strpos($tag, $match)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Inject criticalCss that we placed in admin
     *
     * @return string
     */
    private function injectCriticalCss()
    {
        if (isset($_GET['no_critical_css']) && $_GET['no_critical_css'] == 1) { // phpcs:ignore
            return $this->content;
        }

        if (isset($this->critical->critical_css) && isset($this->critical->status) && $this->critical->status == 'success') {
            $file_url = TWO_CACHE_URL . 'critical/' . $this->critical->critical_css;
            $file_dir = TWO_CACHE_DIR . 'critical/' . $this->critical->critical_css;

            if (file_exists($file_dir)) {
                $critical_content = file_get_contents($file_dir); // phpcs:ignore
                $critical_content = OptimizerUtils::replace_bg($critical_content);

                if (!empty($critical_content)) {
                    $critical_styles = '<style class="two_critical_css"' . ('true' == $this->TwoSettings->get_settings('two_critical_remove') ? ' id="two_critical_css"' : '') . ' type="text/css">' . $critical_content . '</style>';
                }
            }

            if (isset($critical_styles)) {
                $this->two_critical_connection_data['critical_css'] = true;

                $init_uncritical = false;

                if ($this->use_uncritical && isset($this->critical->uncritical_css) && isset($this->critical->critical_fonts)) {
                    $this->two_async_css_arr = [];
                    $this->two_async_css_arr[] = [
                        'url' => TWO_CACHE_URL . 'critical/' . $this->critical->uncritical_css,
                        'media' => 'all',
                        'uid' => ''
                    ];
                    $this->critical_fonts_arr = $this->critical->critical_fonts;
                    $init_uncritical = true;
                }

                $critical_font_css = '';

                if (isset($this->critical->critical_fonts) && is_array($this->critical->critical_fonts) && !$init_uncritical) {
                    $this->two_critical_connection_data['critical_fonts'] = true;

                    foreach ($this->critical->critical_fonts as $critical_font) {
                        if (isset($critical_font->font_face)) {
                            $critical_font_css .= ' ' . $critical_font->font_face;
                        }
                    }
                }

                if (empty($critical_font_css)) {
                    $this->two_critical_connection_data['critical_fonts'] = false;
                }

                if (!empty($this->TwoSettings->get_settings('two_async_font'))) {
                    $critical_font_css = $this->addAttrDisplaySwap($critical_font_css);
                }
                $critical_font_css = '<style class="two_critical_font_css" type="text/css">' . $critical_font_css . '</style>';

                if ($this->critical->uncritical_load_type === 'not_load' || $init_uncritical) {
                    if (preg_match_all('#(<style[^>]*>.*</style>)|(<link[^>]*stylesheet[^>]*>)#Usmi', $this->content, $matches)) {
                        foreach ($matches[0] as $tag) {
                            if (is_array($this->dontmove) && !empty($this->dontmove)) {
                                foreach ($this->dontmove as $ex_el) {
                                    if (false !== strpos($tag, $ex_el)) {
                                        continue 2;
                                    }
                                }
                            }
                            $this->content = str_replace($tag, '', $this->content);
                            $this->cacheStructure->addToTagsToReplace($tag, '');
                        }
                    }
                }

                if ($this->critical->critical_enabled) {
                    $this->content = OptimizerUtils::inject_in_html($this->content, $critical_styles, ['</head>', 'before']);
                }

                if ($this->critical->critical_font_enabled) {
                    $this->content = OptimizerUtils::inject_in_html($this->content, $critical_font_css, ['</head>', 'before']);
                }

                if ($this->critical->critical_enabled) {
                    $this->cacheStructure->addToTagsToAdd($critical_styles, ['</head>', 'before']);
                }

                if ($this->critical->critical_font_enabled) {
                    $this->cacheStructure->addToTagsToAdd($critical_font_css, ['</head>', 'before']);
                }
            }
        }

        return $this->content;
    }

    private function addAttrDisplaySwap($critical_font_css)
    {
        $critical_font_css = trim($critical_font_css);
        $allFontFaces = explode('@font-face', $critical_font_css);
        $sepFontFaces = [];

        foreach ($allFontFaces as $fontFace) {
            $fontFace = trim($fontFace);

            if (empty($fontFace)) {
                continue;
            }
            $positionStart = strpos($fontFace, 'font-display:');

            if (!$positionStart) {
                $mid = strpos($fontFace, '}');
                $fontFace = substr_replace($fontFace, ';font-display: swap}', $mid);
            } else {
                $mid = substr($fontFace, $positionStart + 13);
                $positionEnd = strpos($mid, ';');

                if (!$positionEnd) {
                    $positionEnd = strpos($mid, '}');
                }
                $fontDisplay = substr($mid, 0, $positionEnd);

                if ($fontDisplay !== 'swap') {
                    $fontFace = str_replace($fontDisplay, 'swap', $fontFace);
                }
            }
            $sepFontFaces[] = $fontFace;
        }
        $allFontFaces = implode('@font-face', $sepFontFaces);

        if (!empty($allFontFaces)) {
            $allFontFaces = '@font-face' . $allFontFaces;
        }

        return $allFontFaces;
    }
}
