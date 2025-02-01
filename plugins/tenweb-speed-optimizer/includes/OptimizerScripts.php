<?php

namespace TenWebOptimizer;

use Exception;
use JSMin\JSMin;
use MatthiasMullie\Minify\JS;

if (!defined('ABSPATH')) {
    exit;
}

class OptimizerScripts extends OptimizerBase
{
    const TWO_DELAYED_JS_ATTRIBUTE = 'data-twodelayedjs';

    const TWO_NO_DELAYED_JS_ATTRIBUTE = 'data-two-no-delay';

    const TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE = 'data-pagespeed-no-defer';

    private $scripts = [];

    private $move = [
        'first' => [],
        'last' => [],
    ];

    private $dontmove = [
        'document.write',
        'html5.js',
        'show_ads.js',
        'google_ad',
        'histats.com/js',
        'statcounter.com/counter/counter.js',
        'ws.amazon.com/widgets',
        'media.fastclick.net',
        '/ads/',
        'comment-form-quicktags/quicktags.php',
        'edToolbar',
        'intensedebate.com',
        'scripts.chitika.net/',
        '_gaq.push',
        'jotform.com/',
        'admin-bar.min.js',
        'GoogleAnalyticsObject',
        'plupload.full.min.js',
        'syntaxhighlighter',
        'adsbygoogle',
        'gist.github.com',
        '_stq',
        'nonce',
        'post_id',
        'data-noptimize',
        'logHuman',
        self::TWO_NO_DELAYED_JS_ATTRIBUTE,
        'two_exclude_inline',
        'data-two_exclude'
    ];

    private $domove = [
        'gaJsHost',
        'load_cmc',
        'jd.gallery.transitions.js',
        'swfobject.embedSWF(',
        'tiny_mce.js',
        'tinyMCEPreInit.go',
    ];

    private $domovelast = [
        'addthis.com',
        '/afsonline/show_afs_search.js',
        'disqus.js',
        'networkedblogs.com/getnetworkwidget',
        'infolinks.com/js/',
        'jd.gallery.js.php',
        'jd.gallery.transitions.js',
        'swfobject.embedSWF(',
        'linkwithin.com/widget.js',
        'tiny_mce.js',
        'tinyMCEPreInit.go',
    ];

    private $dontmoveExtended = [
        'document.write',
        'google_ad',
        'edToolbar',
        'gtag',
        '_gaq.push',
        '_gaLt',
        'GoogleAnalyticsObject',
        'syntaxhighlighter',
        'adsbygoogle',
        'ci_cap_',
        '_stq',
        'nonce',
        'post_id',
        'LogHuman',
        'idcomments_acct',
        'ch_client',
        'sc_online_t',
        '_stq',
        'bannersnack_embed',
        'vtn_player_type',
        'ven_video_key',
        'ANS_customer_id',
        'tdBlock',
        'tdLocalCache',
        'wpRestNonce',
        '"url":',
        'lazyLoadOptions',
        'adthrive',
        'loadCSS',
        'google_tag_params',
        'clicky_custom',
        'clicky_site_ids',
        'NSLPopupCenter',
        '_paq',
        'gtm',
        'dataLayer',
        'RecaptchaLoad',
        'WPCOM_sharing_counts',
        'jetpack_remote_comment',
        'subscribe-field',
        'contextly',
        '_mmunch',
        'gt_request_uri',
        'doGTranslate',
        'docTitle',
        'bs_ajax_paginate_',
        'bs_deferred_loading_',
        'theChampRedirectionUrl',
        'theChampFBCommentUrl',
        'theChampTwitterRedirect',
        'theChampRegRedirectionUrl',
        'ESSB_CACHE_URL',
        'oneall_social_login_providers_',
        'betterads_screen_width',
        'woocommerce_wishlist_add_to_wishlist_url',
        'arf_conditional_logic',
        'heateorSsHorSharingShortUrl',
        'TL_Const',
        'bimber_front_microshare',
        'setAttribute("id"',
        'setAttribute( "id"',
        'TribeEventsPro',
        'peepsotimedata',
        'wphc_data',
        'hc_rand_id',
        'RBL_ADD',
        'AfsAnalyticsObject',
        '_thriveCurrentPost',
        'esc_login_url',
        'fwduvpMainPlaylist',
        'Bibblio.initRelatedContent',
        'showUFC()',
        '#iphorm-',
        '#fancy-',
        'ult-carousel-',
        'theChampLJAuthUrl',
        'f._fbq',
        'Insticator',
        'w2dc_js_objects',
        'cherry_ajax',
        'ad_block_',
        'elementorFrontendConfig',
        'zeen_',
        'disqusIdentifier',
        'currentAjaxUrl',
        'geodir_event_call_calendar_',
        'atatags-',
        'hbspt.forms.create',
        'function(c,h,i,m,p)',
        'dataTable({',
        'rankMath = {',
        '_atrk_opts',
        'quicklinkOptions',
        'ct_checkjs_',
        'WP_Statistics_http',
        'penci_block_',
        'omapi_localized',
        'omapi_data',
        'OptinMonsterApp',
        'tminusnow',
        'nfForms',
        'galleries.gallery_',
        'wcj_evt.prodID',
        'advads_tracking_ads',
        'advadsGATracking.postContext',
        'woopack_config',
        'ulp_content_id',
        'wp-cumulus/tagcloud.swf?r=',
        'ctSetCookie(\'ct_checkjs\'',
        'woof_really_curr_tax',
        'uLogin.customInit',
        'i18n_no_matching_variations_text',
        'alsp_map_markers_attrs',
        'var inc_opt =',
        'iworks_upprev',
        'yith_wcevti_tickets',
        'window.metrilo.ensure_cbuid',
        'metrilo.event',
        'wordpress_page_root',
        'wcct_info',
        'Springbot.product_id',
        'pysWooProductData',
        'dfd-heading',
        'owl=$("#',
        'penci_megamenu',
        'fts_security',
        'algoliaAutocomplete',
        'avia_framework_globals',
        'tabs.easyResponsiveTabs',
        'searchlocationHeader',
        'yithautocomplete',
        'data-parallax-speed',
        'currency_data=',
        'cedexisData',
        'function reenableButton',
        '#wpnbio-show',
        'e.Newsletter2GoTrackingObject',
        'var categories_',
        '"+nRemaining+"',
        'cartsguru_cart_token',
        'after_share_easyoptin',
        'location_data.push',
        'thirstyFunctions.isThirstyLink',
        'styles: \' #custom-menu-',
        'function svc_center_',
        '#svc_carousel2_container_',
        'advads.move',
        'elementid',
        'advads_has_ads',
        'wpseo_map_init',
        'mdf_current_page_url',
        'tptn_tracker',
        'dpsp_pin_button_data',
        'searchwp_live_search_params',
        'wpp_params',
        'top.location,thispage',
        'selection+pagelink',
        'ic_window_resolution',
        'PHP.wp_p_id',
        'ShopifyBuy.UI.onReady(client)',
        'orig_request_uri',
        'gie.widgets.load',
        'Adman.Flash',
        'PHP.wp_p_id',
        'window.broadstreetKeywords',
        'var productId =',
        'var flatsomeVars',
        'wc_product_block_data',
        'static.mailerlite.com',
        'amzn_assoc',
        '_bs_getParameterByName',
        '_stq.push',
        'h._remove',
        'var FlowFlowOpts',
        'var WCPFData =',
        'var _beeketing',
        'var _statcounter',
        'var actions =',
        'var current_url',
        'var object_name',
        'var the_ajax_script',
        'var wc_cart_fragments_params',
        'var woocommerce_params',
        'var wpml_cookies',
        'wc_add_to_cart_params',
        'window.broadstreetKeywords',
        'window.wc_ga_pro.available_gateways',
        'xa.prototype',
        'HOUZEZ_ajaxcalls_vars',
        'w2dc_maps_objects',
        'w2dc_controller_args_array',
        'w2dc_map_markers_attrs',
        'YT.Player',
        'WPFC.data',
        'function current_video_',
        'var videodiv',
        'var slider_wppasrotate',
        'wppas_ga',
        'var blockClass',
        'tarteaucitron',
        'pw_brand_product_list',
        'tminusCountDown',
        'pysWooSelectContentData',
        'wpvq_ans89733',
        '_isp_version',
        'price_range_data',
        'window.FeedbackCompanyWidgets',
        'woocs_current_currency',
        'woo_variation_swatches_options',
        'woocommerce_price_slider_params',
        'scriptParams',
        'form-adv-pagination',
        'borlabsCookiePrioritize',
        'urls_wpwidgetpolylang',
        'quickViewNonce',
        'frontendscripts_params',
        'nj-facebook-messenger',
        'var fb_mess_position',
        'init_particles_row_background_script',
        'setREVStartSize',
        'fl-node',
        'PPAccordion',
        'soliloquy_',
        'wprevpublicjs_script_vars',
        'DTGS_NONCE_FRONTEND',
        'et_animation_data',
        'archives-dropdown',
        'loftloaderCache',
        'SmartSliderSimple',
        'var nectarLove',
        'var incOpt',
        'RocketBrowserCompatibilityChecker',
        'RocketPreloadLinksConfig',
        'placementVersionId',
        'var useEdit',
        'var DTGS_NONCE_FRONTEND',
        'n2jQuery',
        'et_core_api_spam_recaptcha',
        'cnArgs',
        '__CF$cv$params',
        'trustbox_settings',
        'aepro',
        'cdn.jst.ai',
        'w2dc_fields_in_categories',
        'aepc_pixel',
        'avadaWooCommerceVars',
        'var isb',
        'fcaPcPost',
        'csrf_token',
        'icwp_wpsf_vars_lpantibot',
        'wpvViewHead',
        'ed_school_plugin',
        'aps_comp_',
        'guaven_woos',
        '__lm_redirect_to',
        '__wpdm_view_count',
        'bookacti.booking_system',
        'nfFrontEnd',
        'view_quote_cart_link',
        '__eae_decode_emails',
        'divioverlays_ajaxurl',
        'var _EPYT_',
        '#ins-heading-',
        '#ins-button-',
        'tve_frontend_options',
        'lb24.src',
        'amazon_Login_accessToken',
        'porto_infinite_scroll',
        '.adace-loader-',
        'adace_load_',
        'tagGroupsAccordiontaggroupscloudaccordion',
        'tagGroupsTabstaggroupscloudtabs',
        'jrRelatedWidgets',
    ];

    private $delayed_js = [];

    public $cdn_url = '';

    private $aggregate = false;

    private $trycatch = false;

    private $alreadyminified = false;

    private $forcehead = true;

    private $include_inline = false;

    private $jscode = '';

    private $hashes = [];

    private $url = '';

    private $restofcontent = '';

    private $md5hash = '';

    private $whitelist = '';

    private $jsremovables = [];

    private $inject_min_late = '';

    private $minify_excluded = true;

    /**
     * @var bool
     */
    private $delay_js_execution = false;

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
    private $TwoSettings;

    /**
     * @var bool
     */
    private $delay_all_js_execution;

    public $two_js_list = [];

    public $two_js_list_excluded = [];

    private $exclude_blob_list = [
        'adsbygoogle.js'
    ];

    public function __construct($content, $cacheStructure)
    {
        global $TwoSettings;
        $this->TwoSettings = $TwoSettings;
        parent::__construct($content);
        $this->cacheStructure = $cacheStructure;
    }

    // Reads the page and collects script tags.
    public function read($options)
    {
        $exclJSArr = [];

        // Determine whether we're doing JS-files aggregation or not.
        if (isset($options['aggregate'])) {
            $this->aggregate = $options['aggregate'];
        }

        // Returning true for "dontaggregate" turns off aggregation.
        // include inline?
        if ($options['include_inline'] && $this->aggregate) {
            $this->include_inline = true;
        }
        $this->inject_min_late = true;
        // Determine whether excluded files should be minified if not yet so.
        // phpcs:ignore Squiz.PHP.CommentedOutCode.Found
        /*if ( !$options['minify_excluded'] && $options['aggregate'] ) {
            $this->minify_excluded = FALSE;
        }*/
        $this->minify_excluded = $options['minify_excluded'];

        if ($this->aggregate) {
            $this->minify_excluded = false;
        }

        // get extra exclusions settings or filter.
        $excludeJS = $options['js_exclude'];

        if ('' !== $excludeJS) {
            if (is_array($excludeJS)) {
                if (($removeKeys = array_keys($excludeJS, 'remove')) !== false) {
                    foreach ($removeKeys as $removeKey) {
                        unset($excludeJS[$removeKey]);
                        $this->jsremovables[] = $removeKey;
                    }
                }
                $exclJSArr = array_keys($excludeJS);
            } else {
                $exclJSArr = array_filter(array_map('trim', explode(',', $excludeJS)));
            }

            $this->dontmove = array_merge($exclJSArr, $this->dontmove);
        }
        $arrayFromHook = apply_filters('two_modify_exclude_js_from_aggregation', $this->dontmove, 10, 1);

        if (is_array($arrayFromHook)) {
            $this->dontmove = $arrayFromHook;
        }

        if ($options['use_extended_exception_list_js']) {
            $this->dontmove = array_unique(array_merge($this->dontmoveExtended, $this->dontmove));
        }

        // Should we add delay execution
        if ($options['delay_js_execution']) {
            $this->delay_js_execution = true;
        }

        // Should we add delay execution
        if ($options['delay_all_js_execution']) {
            $this->delay_all_js_execution = true;
        }

        // delay js execution.
        $delayJSExecutionList = $options['delayed_js_execution_list'];

        if ('' !== $delayJSExecutionList) {
            $delayJSExecution = array_filter(array_map('trim', explode(',', $delayJSExecutionList)));
            $this->delayed_js = array_merge($delayJSExecution, $this->delayed_js);
        }

        $delayJSExecutionFromHook = apply_filters('two_modify_delay_execution', $this->delayed_js, 10, 1);

        if (is_array($delayJSExecutionFromHook)) {
            $this->delayed_js = $delayJSExecutionFromHook;
        }

        // Should we add try-catch?
        if ($options['trycatch']) {
            $this->trycatch = true;
        }

        // force js in head?
        if ($options['forcehead']) {
            $this->forcehead = true;
        } else {
            $this->forcehead = false;
        }
        // get cdn url.
        $this->cdn_url = $options['cdn_url'];
        // noptimize me.
        $this->content = $this->hide_noptimize($this->content);
        // Save IE hacks.
        $this->content = $this->hide_iehacks($this->content);
        // comments.
        $this->content = $this->hide_comments($this->content);

        // Get script files.
        if (preg_match_all('#<script.*</script>#Usmi', $this->content, $matches)) {
            if ($this->delay_all_js_execution) {
                $exclude_delay_js = ExcludeJsFromDelay::get_exclusion_list($options, $this->content);
                $excludeJSLoadNormally = array_merge(explode(',', $options['load_excluded_js_normally']), ExcludeJsFromDelay::EXCLUDE_LIST['types']); //do not make blob templates, schema.org and other excluded types

                foreach ($matches[0] as $tag) {
                    $delay_uid = uniqid('two_', false);

                    if (false !== strpos($tag, OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE)) {
                        continue;
                    }

                    $excluded_script = false;

                    foreach ($exclude_delay_js as $type) {
                        if (!empty($type) && preg_match('~' . $type . '~', $tag)) {
                            $excluded_script = true;
                            break;
                        }
                    }
                    $load_excluded_script_normally = false;

                    foreach ($excludeJSLoadNormally as $type) {
                        if (!empty($type) && false !== strpos($tag, $type)) {
                            $load_excluded_script_normally = true;
                            break;
                        }
                    }

                    if ($excluded_script && ($this->TwoSettings->get_settings('two_load_excluded_js_via_worker') != 'on' || $load_excluded_script_normally)) {
                        continue;
                    }

                    preg_match('#<script[^>]*id=("|\')([^>]*)("|\')#Usmi', $tag, $source);
                    $script_id = '';

                    if (isset($source[2])) {
                        $script_id = $source[2];
                    }

                    if (preg_match('#<script[^>]*src=("|\')([^>]*)("|\')#Usmi', $tag, $source)) {
                        if (isset($source[2])) {
                            $dealy_script_data = [
                                'inline' => false,
                                'url' => $source[2],
                                'id' => $script_id,
                                'uid' => $delay_uid,
                                'exclude_blob' => false,
                            ];

                            foreach ($this->exclude_blob_list as $exclude_blob) {
                                if (!empty($exclude_blob) && false !== strpos($source[2], $exclude_blob)) {
                                    $dealy_script_data['exclude_blob'] = true;
                                    break;
                                }
                            }

                            if ($excluded_script) {
                                $dealy_script_data['excluded_from_delay'] = true;
                                $this->two_js_list_excluded[] = $dealy_script_data;
                            } else {
                                $dealy_script_data['excluded_from_delay'] = false;
                                $this->two_js_list[] = $dealy_script_data;
                            }
                        }
                        $new_tag = str_replace(['src', '<script'], ['data-two_delay_src', '<script data-two_delay_id="' . $delay_uid . '"'], $tag);
                    } else {
                        preg_match('#<script.*>(.*)</script>#Usmi', $tag, $code);

                        if (isset($code[1])) {
                            // Restore comments to fix inline js containing html comment.
                            $js_code = $this->restore_comments($code[1]);
                            // Encode the js to keep unicode characters after decode.
                            $inline_code = base64_encode(rawurlencode($js_code));
                            $dealy_script_data = [
                                'inline' => true,
                                'code' => $inline_code,
                                'id' => $script_id,
                                'uid' => $delay_uid,
                                'exclude_blob' => false,
                            ];

                            if ($excluded_script) {
                                $dealy_script_data['excluded_from_delay'] = true;
                                $this->two_js_list_excluded[] = $dealy_script_data;
                            } else {
                                $dealy_script_data['excluded_from_delay'] = false;
                                $this->two_js_list[] = $dealy_script_data;
                            }
                        }
                        $new_tag = str_replace(['<script', $code[1]], ["<script data-two_delay_src='inline' data-two_delay_id=\"" . $delay_uid . '"', ''], $tag);
                    }

                    // phpcs:ignore Squiz.PHP.CommentedOutCode.Found
                    //$this->content = str_replace($tag , $new_tag, $this->content);
                    $pos = strpos($this->content, $tag);

                    if ($pos !== false) {
                        $this->content = substr_replace($this->content, $new_tag, $pos, strlen($tag));
                    }
                    $this->cacheStructure->addToTagsToReplace($tag, $new_tag);
                }

                return true;
            }

            foreach ($matches[0] as $tag) {
                // only consider script aggregation for types whitelisted in should_aggregate-function or if it is for delayed
                $should_aggregate = $this->should_aggregate($tag);

                if (! ($this->delay_js_execution && $this->isfordelay($tag, $this->delayed_js)) && !$should_aggregate) {
                    $tag = '';
                    continue;
                }

                if (preg_match('#<script[^>]*src=("|\')([^>]*)("|\')#Usmi', $tag, $source)) {
                    // non-inline script.
                    if ($this->isremovable($tag, $this->jsremovables)) {
                        $this->content = str_replace($tag, '', $this->content);
                        $this->cacheStructure->addToTagsToReplace($tag, '');
                        continue;
                    }
                    $origTag = null;
                    $url = current(explode('?', $source[2], 2));

                    if ($this->delay_js_execution && $this->isfordelay($url, $this->delayed_js)) {
                        // phpcs:ignore Squiz.PHP.CommentedOutCode.Found
                        //$newTag = '<script type="text/javascript" '.self::TWO_DELAYED_JS_ATTRIBUTE.'="'.$url.'"></script>';
                        $newTag = str_replace('src=', self::TWO_DELAYED_JS_ATTRIBUTE . '=', $tag);

                        $this->content = str_replace($tag, $newTag, $this->content);
                        $this->cacheStructure->addToTagsToReplace($tag, $newTag);

                        continue;
                    }
                    $path = $this->getpath($url);

                    if (false !== $path && preg_match('#\.js$#', $path) && $this->ismergeable($tag)) {
                        // ok to optimize, add to array.
                        $this->scripts[md5($path)] = $path;
                    } else {
                        $origTag = $tag;
                        $newTag = $tag;

                        // non-mergeable script (excluded or dynamic or external).
                        if (is_array($exclJSArr)) {
                            if (OptimizerUtils::is_pagespeed_js_defer_enabled()) {
                                // should we add flags to disable async loading?
                                foreach ($exclJSArr as $exclTag) {
                                    if (false !== strpos($origTag, $exclTag)) {
                                        $newTag = str_replace('<script ', '<script ' . esc_attr(OptimizerScripts::TWO_DISABLE_PAGESPEED_DEFER_ATTRIBUTE) . ' ', $newTag);
                                    }
                                }
                            } else {
                                //todo check this, there has to be a bug
                                // should we add flags to enable async loading?
                                if (is_array($excludeJS)) {
                                    foreach ($excludeJS as $exclTag => $exclFlags) {
                                        if (false !== strpos($origTag, $exclTag) && in_array($exclFlags, ['async', 'defer'])) {
                                            $newTag = str_replace('<script ', '<script ' . $exclFlags . ' ', $newTag);
                                        }
                                    }
                                }
                            }
                        }

                        // Should we minify the non-aggregated script?
                        // -> if aggregate is on and exclude minify is on
                        // -> if aggregate is off and the file is not in dontmove.
                        if ($path && $this->minify_excluded) {
                            $consider_minified_array = false;

                            if ((false === $this->aggregate && str_replace($this->dontmove, '', $path) === $path) || (true === $this->aggregate && (false === $consider_minified_array || str_replace($consider_minified_array, '', $path) === $path))) {
                                $minified_url = $this->minify_single($path);

                                // replace orig URL with minified URL from cache if so.
                                if (!empty($minified_url)) {
                                    $newTag = str_replace($url, $minified_url, $newTag);
                                }
                            }
                        }

                        if ($this->ismovable($newTag)) {
                            // can be moved, flags and all.
                            if ($this->movetolast($newTag)) {
                                $this->move['last'][] = $newTag;
                            } else {
                                $this->move['first'][] = $newTag;
                            }
                        } else {
                            // cannot be moved, so if flag was added re-inject altered tag immediately.
                            if ($origTag !== $newTag) {
                                $this->content = str_replace($origTag, $newTag, $this->content);
                                $this->cacheStructure->addToTagsToReplace($origTag, $newTag);

                                $origTag = '';
                            }
                            // and forget about the $tag (not to be touched any more).
                            $tag = '';
                        }
                    }
                } else {
                    //this tag is for delay
                    if ($this->delay_js_execution && $this->isfordelay($tag, $this->delayed_js)) {
                        $type = OptimizerUtils::get_javascipt_type($tag);
                        preg_match('#<script.*>(.*)</script>#Usmi', $tag, $code);

                        if (isset($code[1])) {
                            $newTag = '<script ' . self::TWO_DELAYED_JS_ATTRIBUTE . '="data:' . $type . ';base64,' . base64_encode($code[1]) . '"></script>';
                            $this->content = str_replace($tag, $newTag, $this->content);
                            $this->cacheStructure->addToTagsToReplace($tag, $newTag);

                            continue;
                        }
                    }

                    // Inline script.
                    if ($this->isremovable($tag, $this->jsremovables)) {
                        $this->content = str_replace($tag, '', $this->content);
                        $this->cacheStructure->addToTagsToReplace($tag, '');

                        continue;
                    }
                    // unhide comments, as javascript may be wrapped in comment-tags for old times' sake.
                    $tag = $this->restore_comments($tag);

                    if ($this->include_inline && $this->ismergeable($tag)) {
                        preg_match('#<script.*>(.*)</script>#Usmi', $tag, $code);
                        $code = preg_replace('#.*<!\[CDATA\[(?:\s*\*/)?(.*)(?://|/\*)\s*?\]\]>.*#sm', '$1', $code[1]);
                        $code = preg_replace('/(?:^\\s*<!--\\s*|\\s*(?:\\/\\/)?\\s*-->\\s*$)/', '', $code);
                        $this->scripts[md5($code)] = 'INLINE;' . $code;
                    } else {
                        // Can we move this?
                        $twoptimize_js_moveable = '';

                        //todo refactor this
                        if ($this->ismovable($tag) || '' !== $twoptimize_js_moveable) {
                            if ($this->movetolast($tag) || 'last' === $twoptimize_js_moveable) {
                                $this->move['last'][] = $tag;
                            } else {
                                $this->move['first'][] = $tag;
                            }
                        } else {
                            // We shouldn't touch this.
                            $tag = '';
                        }
                    }
                    // Re-hide comments to be able to do the removal based on tag from $this->content.
                    $tag = $this->hide_comments($tag);
                }
                //Remove the original script tag.
                $this->content = str_replace($tag, '', $this->content);
                $this->cacheStructure->addToTagsToReplace($tag, '');
            }

            return true;
        }

        // No script files, great ;-)
        return false;
    }

    /**
     * Determines wheter a certain `<script>` $tag should be aggregated or not.
     * We consider these as "aggregation-safe" currently:
     * - script tags without a `type` attribute
     * - script tags with these `type` attribute values: `text/javascript`, `text/ecmascript`, `application/javascript`,
     * - it is not delayed script
     * and `application/ecmascript`
     * Everything else should return false.
     *
     * @see https://developer.mozilla.org/en/docs/Web/HTML/Element/script#attr-type
     *
     * @param string $tag
     *
     * @return bool
     */
    public function should_aggregate($tag)
    {
        // We're only interested in the type attribute of the <script> tag itself, not any possible
        // inline code that might just contain the 'type=' string...
        if (false !== strpos($tag, OptimizerScripts::TWO_NO_DELAYED_JS_ATTRIBUTE)) {
            return false;
        }
        $tag_parts = [];
        preg_match('#<(script[^>]*)>#i', $tag, $tag_parts);
        $tag_without_contents = null;

        if (!empty($tag_parts[1])) {
            $tag_without_contents = $tag_parts[1];
        }
        $has_type = (strpos($tag_without_contents, 'type') !== false);
        $type_valid = false;

        if ($has_type) {
            $type_valid = (bool) preg_match('/type\s*=\s*[\'"]?(?:text|application)\/(?:javascript|ecmascript)[\'"]?/i', $tag_without_contents);
        }
        $should_aggregate = false;

        if (!$has_type || $type_valid) {
            $should_aggregate = true;
        }

        return $should_aggregate;
    }

    //Joins and optimizes JS
    public function optimize()
    {
        $two_change_minify = $this->TwoSettings->get_settings('two_change_minify');

        if (!empty($this->scripts)) {
            foreach ($this->scripts as $hash => $script) {
                if (preg_match('#^INLINE;#', $script)) {
                    // Inline script
                    $script = preg_replace('#^INLINE;#', '', $script);
                    $script = rtrim($script, ";\n\t\r") . ';';

                    // phpcs:ignore Squiz.PHP.CommentedOutCode.Found
                    // Add try catch
                    if ($this->trycatch) {
                        $script = 'try{' . $script . '}catch(e){}';
                    }
                    $tmpscript = $this->js_snippetcacher($script, '', $hash);

                    if (!empty($tmpscript)) {
                        $script = $tmpscript;
                        $this->alreadyminified = true;
                    }
                    $this->jscode .= "\n" . $script;
                    $this->hashes[] = $hash;
                } else {
                    // External script
                    if (false !== $script && file_exists($script) && is_readable($script)) {
                        $scriptsrc = file_get_contents($script); // phpcs:ignore
                        $scriptsrc = preg_replace('/\x{EF}\x{BB}\x{BF}/', '', $scriptsrc);
                        $scriptsrc = rtrim($scriptsrc, ";\n\t\r") . ';';

                        // phpcs:ignore Squiz.PHP.CommentedOutCode.Found
                        // Add try catch
                        if ($this->trycatch) {
                            $scriptsrc = 'try{' . $scriptsrc . '}catch(e){}';
                        }
                        $tmpscriptsrc = $this->js_snippetcacher($scriptsrc, '', $hash);

                        if (!empty($tmpscriptsrc)) {
                            $scriptsrc = $tmpscriptsrc;
                            $this->alreadyminified = true;
                        } else {
                            if ($this->can_inject_late($script)) {
                                $scriptsrc = self::build_injectlater_marker($script, md5($scriptsrc));
                            }
                        }
                        $this->jscode .= "\n" . $scriptsrc;
                        $this->hashes[] = $hash;
                    }
                }
            }
        }

        // Check for already-minified code
        $this->md5hash = md5($this->jscode);

        if (true !== $this->alreadyminified) {
            if ($two_change_minify == false || $two_change_minify == 'JSMin') {
                try {
                    $tmp_jscode = trim(JSMin::minify($this->jscode));
                } catch (Exception $e) {
                    $tmp_jscode = $this->jscode;
                }
            } else {
                $minifier = new JS($this->jscode);
                $tmp_jscode = $minifier->minify();
            }

            if (!empty($tmp_jscode)) {
                $this->jscode = $tmp_jscode;
                unset($tmp_jscode);
            }
            $this->jscode = $this->inject_minified($this->jscode);

            return true;
        }

        return true;
    }

    // Caches the JS in uncompressed, deflated and gzipped form.
    public function cache()
    {
        $cache = new OptimizerCache(null, 'js');

        if (!empty($this->jscode)) {
            // Cache our code
            $cache->cache($this->jscode, 'text/javascript');
        }
        $this->url = TWO_CACHE_URL . $cache->getname();
        $this->url = $this->url_replace_cdn($this->url);
    }

    // Returns the content
    public function getcontent()
    {
        // Restore the full content
        if (!empty($this->restofcontent)) {
            $this->content .= $this->restofcontent;
            $this->restofcontent = '';
        }

        // Add the scripts taking forcehead/ deferred (default) into account
        if ($this->forcehead) {
            $replaceTag = [ '</head>', 'before' ];
            $defer = '';
        } else {
            $replaceTag = [ '</body>', 'before' ];
            $defer = 'defer ';
        }

        $bodyreplacementpayload = '<script type="text/javascript" ' . $defer . 'src="' . $this->url . '"></script>';
        $bodyreplacement = implode('', $this->move['first']);
        $bodyreplacement .= $bodyreplacementpayload;
        $bodyreplacement .= implode('', $this->move['last']);

        if (strlen($this->jscode) > 0) {
            $this->content = OptimizerUtils::inject_in_html($this->content, $bodyreplacement, $replaceTag);
            $this->cacheStructure->addToTagsToAdd($bodyreplacement, $replaceTag);
        }
        // Restore comments.
        $this->content = $this->restore_comments($this->content);
        // Restore IE hacks.
        $this->content = $this->restore_iehacks($this->content);
        // Restore noptimize.
        $this->content = $this->restore_noptimize($this->content);

        if (!empty($this->two_js_list) && $this->delay_all_js_execution) {
            $two_events_after_load = $this->TwoSettings->get_settings('two_events_after_load', []);
            $dispatchEvents = '';

            if (is_array($two_events_after_load)) {
                if (in_array('DOMContentLoaded', $two_events_after_load)) {
                    $dispatchEvents .= 'console.log("Dispatching DOMContentLoaded event");';
                    $dispatchEvents .= 'document.dispatchEvent(new Event("DOMContentLoaded"));';
                }

                if (in_array('Load', $two_events_after_load)) {
                    $dispatchEvents .= 'console.log("Dispatching Load event");';
                    $dispatchEvents .= 'window.dispatchEvent(new Event("load"));';
                }

                if (in_array('Click', $two_events_after_load)) {
                    $dispatchEvents .= 'console.log("Dispatching ClickOrTouch event");';
                    $dispatchEvents .= 'two_loading_events(two_event);';
                }
            }

            $this->two_js_list[] = [
                'code' => base64_encode('

                if (window.two_page_loaded) {
                    console.log("dispatching events");' .
                    $dispatchEvents . '
                }
                else {
                    console.log("waiting for page to load");
                    window.addEventListener("load", function() {
                        console.log("dispatching events");
                        document.dispatchEvent(new Event("DOMContentLoaded"));
                    });
                }
                '),
                'inline' => true,
                'uid' => 'two_dispatchEvent_script',
            ];
        }
        $two_dispatchEvent_script = '<script data-two_delay_id="two_dispatchEvent_script"></script>';
        // phpcs:ignore Squiz.PHP.CommentedOutCode.Found
        $this->content = OptimizerUtils::inject_in_html($this->content, $two_dispatchEvent_script, [ '</body>', 'before' ]);
        $this->cacheStructure->addToTagsToAdd($two_dispatchEvent_script, [ '</body>', 'before' ]);

        // Return the modified HTML.
        return $this->content;
    }

    // Checks against the white- and blacklists
    private function ismergeable($tag)
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

            // no match with whitelist
            return false;
        } else {
            foreach ($this->domove as $match) {
                if (false !== strpos($tag, $match)) {
                    // Matched something
                    return false;
                }
            }

            if ($this->movetolast($tag)) {
                return false;
            }

            foreach ($this->dontmove as $match) {
                if (false !== strpos($tag, $match)) {
                    // Matched something
                    return false;
                }
            }

            // If we're here it's safe to merge
            return true;
        }
    }

    // Checks agains the blacklist
    private function ismovable($tag)
    {
        if (true !== $this->include_inline) {
            return false;
        }

        foreach ($this->domove as $match) {
            if (false !== strpos($tag, $match)) {
                // Matched something
                return true;
            }
        }

        if ($this->movetolast($tag)) {
            return true;
        }

        foreach ($this->dontmove as $match) {
            if (false !== strpos($tag, $match)) {
                // Matched something
                return false;
            }
        }

        // If we're here it's safe to move
        return true;
    }

    private function movetolast($tag)
    {
        foreach ($this->domovelast as $match) {
            if (false !== strpos($tag, $match)) {
                // Matched. return true
                return true;
            }
        }

        // Should be in 'first'
        return false;
    }

    /**
     * Determines wheter a <script> $tag can be excluded from minification (as already minified) based on:
     * - inject_min_late being active
     * - filename ending in `min.js`
     * - filename matching `js/jquery/jquery.js` (wordpress core jquery, is minified)
     * - filename matching one passed in the consider minified filter
     *
     * @param string $jsPath
     *
     * @return bool
     */
    private function can_inject_late($jsPath)
    {
        $consider_minified_array = false;

        if (true !== $this->inject_min_late) {
            // late-inject turned off
            return false;
        } else {
            if ((false === strpos($jsPath, 'min.js')) && (false === strpos($jsPath, 'wp-includes/js/jquery/jquery.js')) && (str_replace($consider_minified_array, '', $jsPath) === $jsPath)) {
                // file not minified based on filename & filter
                return false;
            } else {
                // phew, all is safe, we can late-inject
                return true;
            }
        }
    }

    /**
     * Minifies a single local js file and returns its (cached) url.
     *
     * @param string $filepath   filepath
     * @param bool   $cache_miss Optional. Force a cache miss. Default false.
     *
     * @return bool|string url pointing to the minified js file or false
     */
    public function minify_single($filepath, $cache_miss = false)
    {
        $two_change_minify = $this->TwoSettings->get_settings('two_change_minify');
        $contents = $this->prepare_minify_single($filepath);

        if (empty($contents)) {
            return false;
        }
        // Check cache.
        $name_prefix = 'minified_' . str_replace('.js', '', basename($filepath));
        $cache = new OptimizerCache(null, 'js', 'all', $name_prefix);

        if ($two_change_minify == false || $two_change_minify == 'JSMin') {
            try {
                $contents = trim(JSMin::minify($contents));
            } catch (Exception $e) {
                $contents = $contents;
            }
        } else {
            $minifier = new JS($contents);
            $contents = $minifier->minify();
        }
        // Store in cache.
        $cache->cache($contents, 'text/javascript');
        $url = $this->build_minify_single_url($cache);

        return $url;
    }

    public function js_snippetcacher($jsin, $jsfilename, $hash)
    {
        $two_change_minify = $this->TwoSettings->get_settings('two_change_minify');

        if ($two_change_minify == false || $two_change_minify == 'JSMin') {
            try {
                $tmp_jscode = trim(JSMin::minify($jsin));
            } catch (Exception $e) {
                $tmp_jscode = $jsin;
            }
        } else {
            $minifier = new JS($jsin);
            $tmp_jscode = $minifier->minify();
        }

        if (! empty($tmp_jscode)) {
            $scriptsrc = $tmp_jscode;
            unset($tmp_jscode);
        } else {
            $scriptsrc = $jsin;
        }
        $last_char = substr($scriptsrc, -1, 1);

        if (';' !== $last_char && '}' !== $last_char) {
            $scriptsrc .= ';';
        }

        return $scriptsrc;
    }
}
