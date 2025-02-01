<?php

namespace TenWebOptimizer;

/*
 * Thin wrapper around css minifiers to avoid rewriting a bunch of existing code.
 */
if (!defined('ABSPATH')) {
    exit;
}

class OptimizerFonts extends OptimizerBase
{
    const TWO_PRELOAD_LINK_TYPES = [
        'preload',
        'preconnect'
    ];

    /**
     * @var bool|mixed|void
     */
    private $linksToPreload;

    private $font_swap;

    private $two_load_fonts_via_webfont;

    private $webFont_list;

    private $google_subsets;

    private $google_fonts;

    private $fontsExtensions = [
        'eot' => 'font/eot',
        'ttf' => 'font/ttf',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
    ];

    private $styleExtensions = [
        'css' => 'text/css',
    ];

    private $scriptExtensions = [
        'js' => 'application/javascript',
    ];

    private $videoExtensions = [
        'avi' => 'video/x-msvideo',
        'mpeg' => 'video/mpeg',
        'ogv' => 'video/ogg',
        'ts' => 'video/mp2t',
        'webm' => 'video/webm',
        '3gp' => 'video/3gpp',
        '3g2' => 'video/3gpp2',
    ];

    /**
     * @var false|mixed|void
     */
    private $linksToPreconnect;

    public function addPreload($option, $type)
    {
        if (empty($option)) {
            return true;
        }

        $links = $this->convertOptionToArray($option);
        $this->injectLinks($type, $links);

        return true;
    }

    /**
     * @param string $preloadType
     * @param array  $links
     */
    private function injectLinks($preloadType, $links)
    {
        if (in_array($preloadType, self::TWO_PRELOAD_LINK_TYPES, false)) { // phpcs:ignore
            if (!empty($links)) {
                $preloadString = '';

                foreach ($links as $link) {
                    $linkWithoutQueryArgs = strpos($link, '?') !== false ? substr($link, 0, strpos($link, '?')) : $link;
                    $linkPath = pathinfo($linkWithoutQueryArgs);

                    if (isset($linkPath['extension'])) {
                        $type = $this->getLinkType($linkPath['extension']);

                        if ($type !== false) {
                            $preloadString .= '<link rel="' . $preloadType . '" as="' . $type[0] . '" href="' . $link . '" type="' . $type[1] . '" crossorigin="anonymous">';
                        } else {
                            $preloadString .= '<link rel="' . $preloadType . '" href="' . $link . '" crossorigin="anonymous">';
                        }
                    }
                }
            }

            if (!empty($preloadString)) {
                $this->content = OptimizerUtils::inject_in_html($this->content, $preloadString, [ '<head', 'after_tag' ]);
            }
        }
    }

    /**
     * Get all google fonts from html combine them add preconnect and remove old ones
     *
     * @return bool
     */
    public function combineGoogleFonts()
    {
        if (!$this->font_swap) {
            return true;
        }

        //check if google fonts are in HTML
        $google_fonts_pattern = '/<link(?:\s+(?:(?!href\s*=\s*)[^>])+)?(?:\s+href\s*=\s*([\'"])(?<url>(?:https?:)?\/\/fonts\.googleapis\.com\/css(?:(?!\1).)+)\1)(?:\s+[^>]*)?>/Umsi';
        $result = preg_match_all($google_fonts_pattern, $this->content, $fonts, PREG_SET_ORDER);

        if (empty($result)) {
            return true;
        }

        //add preconnect for google fonts
        $this->addPreconnect();

        //if more than 1 font is found parse and combine them into 1 <link> tag
        if (count($fonts) > 1) {
            $this->parse_google_fonts($fonts);
            $google_font_tag = $this->get_combined_font_tag();
            $this->content = OptimizerUtils::inject_in_html($this->content, $google_font_tag, [ '<head', 'after_tag' ]);

            foreach ($fonts as $font) {
                $this->remove_from_html($font[0]);
            }
        }
    }

    /**
     * Add preconnect for google fonts
     *
     * @return bool
     */
    public function addPreconnect()
    {
        // check if preconnect already exists
        $preconnect_pattern = "/((<link[^>]*rel=(['\"])preconnect(['\"])[^>]*href=(['\"])(?:https?:)\/\/fonts.gstatic.com[^>]*)|(<link[^>]*href=(['\"])(?:https?:)\/\/fonts.gstatic.com[^>]*rel=(['\"])preconnect(['\"])[^>]*))/Umsi";
        $result = preg_match_all($preconnect_pattern, $this->content, $preconnect, PREG_SET_ORDER);

        if (!empty($result)) {
            return true;
        }

        //if everything is fine add precoonect to final html
        $preconnect_string = '<link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>';
        $this->content = OptimizerUtils::inject_in_html($this->content, $preconnect_string, [ '<head', 'after_tag' ]);
    }

    /**
     * Get Combined tag for all found google fonts
     *
     * @return string
     */
    protected function get_combined_font_tag()
    {
        return '<link rel="preload" as="style" href="https://fonts.googleapis.com/css?family=' . $this->google_fonts . $this->google_subsets . '&display=swap">
<link rel="stylesheet" media="none" onload="this.onload=null;this.removeAttribute(\'media\');" href="https://fonts.googleapis.com/css?family=' . $this->google_fonts . $this->google_subsets . '&display=swap">
<noscript>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=' . $this->google_fonts . $this->google_subsets . '&display=swap">
</noscript>';
    }

    /**
     * Parse all found google fonts
     */
    protected function parse_google_fonts($fonts)
    {
        $fonts_array = [];
        $subsets_array = [];

        foreach ($fonts as $font) {
            $url = html_entity_decode($font[2]);
            $query = wp_parse_url($url, PHP_URL_QUERY);

            if (empty($query)) {
                return;
            }

            $font = wp_parse_args($query);

            if (isset($font['family'])) {
                $font_family = $font['family'];
                $font_family = rtrim($font_family, '%7C');
                $font_family = rtrim($font_family, '|');
                // Add font to the collection.
                $fonts_array[] = rawurlencode(htmlentities($font_family));
            }

            // Add subset to collection.
            if (isset($font['subset'])) {
                $subsets_array[] = rawurlencode(htmlentities($font['subset']));
            }
        }

        // Concatenate fonts tag.
        $this->google_subsets = ! empty($subsets_array) ? '&subset=' . implode(',', array_filter(array_unique($subsets_array))) : '';
        $this->google_fonts = ! empty($fonts_array) ? implode('%7C', array_filter(array_unique($fonts_array))) : '';
    }

    /**
     * Returns array of as and type
     *
     * @return array|bool
     */
    private function getLinkType($extension)
    {
        if (isset($this->fontsExtensions[$extension])) {
            return ['font', $this->fontsExtensions[$extension]];
        }

        if (isset($this->styleExtensions[$extension])) {
            return ['style', $this->styleExtensions[$extension]];
        }

        if (isset($this->scriptExtensions[$extension])) {
            return ['script', $this->scriptExtensions[$extension]];
        }

        if (isset($this->videoExtensions[$extension])) {
            return ['video', $this->videoExtensions[$extension]];
        }

        return false;
    }

    public function getcontent()
    {
        return $this->content;
    }

    public function read($options)
    {
        global $TwoSettings;
        $this->linksToPreload = $TwoSettings->get_settings('two_fonts_to_preload', '');
        $this->linksToPreconnect = $TwoSettings->get_settings('two_fonts_to_preconnect', '');
        $this->font_swap = empty($TwoSettings->get_settings('two_async_font', false)) ? false : true;
        $this->two_load_fonts_via_webfont = empty($TwoSettings->get_settings('two_load_fonts_via_webfont', false)) ? false : true;

        if (isset($options['webFont_list'])) {
            $this->webFont_list = $options['webFont_list'];
        }
    }

    public function optimize()
    {
        $this->addPreload($this->linksToPreload, 'preload');
        $this->addPreload($this->linksToPreconnect, 'preconnect');
        $this->combineGoogleFonts();

        if ($this->two_load_fonts_via_webfont && is_array($this->webFont_list) && !empty($this->webFont_list)) {
            $this->add_WebFont();
        }
    }

    public function add_WebFont()
    {
        $webFont_js = '
        <script ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ' . OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE . '>
            WebFontConfig = {
              google: {families: ' .
                    json_encode($this->webFont_list) // phpcs:ignore
            . "}
            };
            (function () {
              var wf = document.createElement('script');
              wf.src = 'https://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
              wf.type = 'text/javascript';
              wf.async = 'true';
              var s = document.getElementsByTagName('script')[0];
              s.parentNode.insertBefore(wf, s);
            })();
        </script>
        ";
        $this->content = OptimizerUtils::inject_in_html($this->content, $webFont_js, [ '<head', 'after_tag' ]);
    }

    public function cache()
    {
        // TODO: Implement cache() method.
    }

    private function convertOptionToArray($option)
    {
        return array_filter(array_map('trim', explode(',', $option)));
    }
}
