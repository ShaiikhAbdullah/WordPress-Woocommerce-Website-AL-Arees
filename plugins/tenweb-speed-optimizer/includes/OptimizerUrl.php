<?php

namespace TenWebOptimizer;

use Exception;

if (!defined('ABSPATH')) {
    exit;
}

class OptimizerUrl
{
    public const TWO_FASTCGI_NON_CACHED_URLS = [
        '/wp-admin/',
        '/xmlrpc.php',
        'wp-.*.php',
        'feed',
        'index.php',
        'sitemap(_index)?.xml',
        '/store.*',
        '/cart.*',
        '/my-account.*',
        '/checkout.*',
        '/addons.*',
        'well-known',
        'acme-challenge'
    ];

    /* only for 10web.io, specially for Dianna */
    public const TWO_FASTCGI_CACHED_URLS = [
        'wordpress-instagram-feed',
        'wordpress-facebook-feed',
    ];

    public const TWO_FASTCGI_NON_CACHED_PAGES_IF_COOKIE_EXISTS = [
        'comment_author',
        'wordpress_[a-f0-9]+',
        'wordpress_logged_in_',
        'wp-postpass',
        'wordpress_no_cache',
        'woocommerce_cart_hash',
        'fm_cookie_[a-zA-Z0-9]+',
        'woocommerce_items_in_cart',
        'wp_woocommerce_session',
        'woocommerce_recently_viewed',
    ];

    public const EXCLUDED_FROM_FULL_LIST_USER_AGENTS = [
        '^curl',
        'Chrome-Lighthouse'
    ];

    public const TWO_FASTCGI_NON_CACHED_PAGES_IF_USER_AGENT = [
        // Source: https://raw.githubusercontent.com/monperrus/crawler-user-agents/master/crawler-user-agents.json
        'Googlebot\\/',
        'Googlebot-Mobile',
        'Googlebot-Image',
        'Googlebot-News',
        'Googlebot-Video',
        'AdsBot-Google([^-]|$)',
        'AdsBot-Google-Mobile',
        'Feedfetcher-Google',
        'Mediapartners-Google',
        'Mediapartners \\(Googlebot\\)',
        'APIs-Google',
        'bingbot',
        'Slurp',
        '[wW]get',
        'LinkedInBot',
        'Python-urllib',
        'python-requests',
        'aiohttp',
        'httpx',
        'libwww-perl',
        'httpunit',
        'nutch',
        'Go-http-client',
        'phpcrawl',
        'msnbot',
        'jyxobot',
        'FAST-WebCrawler',
        'FAST Enterprise Crawler',
        'BIGLOTRON',
        'Teoma',
        'convera',
        'seekbot',
        'Gigabot',
        'Gigablast',
        'exabot',
        'ia_archiver',
        'GingerCrawler',
        'webmon ',
        'HTTrack',
        'grub.org',
        'UsineNouvelleCrawler',
        'antibot',
        'netresearchserver',
        'speedy',
        'fluffy',
        'findlink',
        'msrbot',
        'panscient',
        'yacybot',
        'AISearchBot',
        'ips-agent',
        'tagoobot',
        'MJ12bot',
        'woriobot',
        'yanga',
        'buzzbot',
        'mlbot',
        'YandexBot',
        'YandexImages',
        'YandexAccessibilityBot',
        'YandexMobileBot',
        'YandexMetrika',
        'YandexTurbo',
        'YandexImageResizer',
        'YandexVideo',
        'YandexAdNet',
        'YandexBlogs',
        'YandexCalendar',
        'YandexDirect',
        'YandexFavicons',
        'YaDirectFetcher',
        'YandexForDomain',
        'YandexMarket',
        'YandexMedia',
        'YandexMobileScreenShotBot',
        'YandexNews',
        'YandexOntoDB',
        'YandexPagechecker',
        'YandexPartner',
        'YandexRCA',
        'YandexSearchShop',
        'YandexSitelinks',
        'YandexSpravBot',
        'YandexTracker',
        'YandexVertis',
        'YandexVerticals',
        'YandexWebmaster',
        'YandexScreenshotBot',
        'purebot',
        'Linguee Bot',
        'CyberPatrol',
        'voilabot',
        'Baiduspider',
        'citeseerxbot',
        'spbot',
        'twengabot',
        'postrank',
        'TurnitinBot',
        'scribdbot',
        'page2rss',
        'sitebot',
        'linkdex',
        'Adidxbot',
        'ezooms',
        'dotbot',
        'Mail.RU_Bot',
        'discobot',
        'heritrix',
        'findthatfile',
        'europarchive.org',
        'NerdByNature.Bot',
        'sistrix crawler',
        'Ahrefs(Bot|SiteAudit)',
        'fuelbot',
        'CrunchBot',
        'IndeedBot',
        'mappydata',
        'woobot',
        'ZoominfoBot',
        'PrivacyAwareBot',
        'Multiviewbot',
        'SWIMGBot',
        'Grobbot',
        'eright',
        'Apercite',
        'semanticbot',
        'Aboundex',
        'domaincrawler',
        'wbsearchbot',
        'summify',
        'CCBot',
        'edisterbot',
        'seznambot',
        'ec2linkfinder',
        'gslfbot',
        'aiHitBot',
        'intelium_bot',
        'facebookexternalhit',
        'Yeti',
        'RetrevoPageAnalyzer',
        'lb-spider',
        'Sogou',
        'lssbot',
        'careerbot',
        'wotbox',
        'wocbot',
        'ichiro',
        'DuckDuckBot',
        'lssrocketcrawler',
        'drupact',
        'webcompanycrawler',
        'acoonbot',
        'openindexspider',
        'gnam gnam spider',
        'web-archive-net.com.bot',
        'backlinkcrawler',
        'coccoc',
        'integromedb',
        'content crawler spider',
        'toplistbot',
        'it2media-domain-crawler',
        'ip-web-crawler.com',
        'siteexplorer.info',
        'elisabot',
        'proximic',
        'changedetection',
        'arabot',
        'WeSEE:Search',
        'niki-bot',
        'CrystalSemanticsBot',
        'rogerbot',
        '360Spider',
        'psbot',
        'InterfaxScanBot',
        'CC Metadata Scaper',
        'g00g1e.net',
        'GrapeshotCrawler',
        'urlappendbot',
        'brainobot',
        'fr-crawler',
        'binlar',
        'SimpleCrawler',
        'Twitterbot',
        'cXensebot',
        'smtbot',
        'bnf.fr_bot',
        'A6-Indexer',
        'ADmantX',
        'Facebot',
        'OrangeBot\\/',
        'memorybot',
        'AdvBot',
        'MegaIndex',
        'SemanticScholarBot',
        'ltx71',
        'nerdybot',
        'xovibot',
        'BUbiNG',
        'Qwantify',
        'archive.org_bot',
        'Applebot',
        'TweetmemeBot',
        'crawler4j',
        'findxbot',
        'S[eE][mM]rushBot',
        'yoozBot',
        'lipperhey',
        'Y!J',
        'Domain Re-Animator Bot',
        'AddThis',
        'Screaming Frog SEO Spider',
        'MetaURI',
        'Scrapy',
        'Livelap[bB]ot',
        'OpenHoseBot',
        'CapsuleChecker',
        'collection@infegy.com',
        'IstellaBot',
        'DeuSu\\/',
        'betaBot',
        'Cliqzbot\\/',
        'MojeekBot\\/',
        'netEstate NE Crawler',
        'SafeSearch microdata crawler',
        'Gluten Free Crawler\\/',
        'Sonic',
        'Sysomos',
        'Trove',
        'deadlinkchecker',
        'Slack-ImgProxy',
        'Embedly',
        'RankActiveLinkBot',
        'iskanie',
        'SafeDNSBot',
        'SkypeUriPreview',
        'Veoozbot',
        'Slackbot',
        'redditbot',
        'datagnionbot',
        'Google-Adwords-Instant',
        'adbeat_bot',
        'WhatsApp',
        'contxbot',
        'pinterest.com.bot',
        'electricmonk',
        'GarlikCrawler',
        'BingPreview\\/',
        'vebidoobot',
        'FemtosearchBot',
        'Yahoo Link Preview',
        'MetaJobBot',
        'DomainStatsBot',
        'mindUpBot',
        'Daum\\/',
        'Jugendschutzprogramm-Crawler',
        'Xenu Link Sleuth',
        'Pcore-HTTP',
        'moatbot',
        'KosmioBot',
        '[pP]ingdom',
        'AppInsights',
        'PhantomJS',
        'Gowikibot',
        'PiplBot',
        'Discordbot',
        'TelegramBot',
        'Jetslide',
        'newsharecounts',
        'James BOT',
        'Bark[rR]owler',
        'TinEye',
        'SocialRankIOBot',
        'trendictionbot',
        'Ocarinabot',
        'epicbot',
        'Primalbot',
        'DuckDuckGo-Favicons-Bot',
        'GnowitNewsbot',
        'Leikibot',
        'LinkArchiver',
        'YaK\\/',
        'PaperLiBot',
        'Digg Deeper',
        'dcrawl',
        'Snacktory',
        'AndersPinkBot',
        'Fyrebot',
        'EveryoneSocialBot',
        'Mediatoolkitbot',
        'Luminator-robots',
        'ExtLinksBot',
        'SurveyBot',
        'NING\\/',
        'okhttp',
        'Nuzzel',
        'omgili',
        'PocketParser',
        'YisouSpider',
        'um-LN',
        'ToutiaoSpider',
        'MuckRack',
        "Jamie's Spider",
        'AHC\\/',
        'NetcraftSurveyAgent',
        'Laserlikebot',
        '^Apache-HttpClient',
        'AppEngine-Google',
        'Jetty',
        'Upflow',
        'Thinklab',
        'Traackr.com',
        'Twurly',
        'Mastodon',
        'http_get',
        'DnyzBot',
        'botify',
        '007ac9 Crawler',
        'BehloolBot',
        'BrandVerity',
        'check_http',
        'BDCbot',
        'ZumBot',
        'EZID',
        'ICC-Crawler',
        'ArchiveBot',
        '^LCC ',
        'filterdb.iss.net\\/crawler',
        'BLP_bbot',
        'BomboraBot',
        'Buck\\/',
        'Companybook-Crawler',
        'Genieo',
        'magpie-crawler',
        'MeltwaterNews',
        'Moreover',
        'newspaper\\/',
        'ScoutJet',
        '(^| )sentry\\/',
        'StorygizeBot',
        'UptimeRobot',
        'OutclicksBot',
        'seoscanners',
        'Hatena',
        'Google Web Preview',
        'MauiBot',
        'AlphaBot',
        'SBL-BOT',
        'IAS crawler',
        'adscanner',
        'Netvibes',
        'acapbot',
        'Baidu-YunGuanCe',
        'bitlybot',
        'blogmuraBot',
        'Bot.AraTurka.com',
        'bot-pge.chlooe.com',
        'BoxcarBot',
        'BTWebClient',
        'ContextAd Bot',
        'Digincore bot',
        'Disqus',
        'Feedly',
        'Fetch\\/',
        'Fever',
        'Flamingo_SearchEngine',
        'FlipboardProxy',
        'g2reader-bot',
        'G2 Web Services',
        'imrbot',
        'K7MLWCBot',
        'Kemvibot',
        'Landau-Media-Spider',
        'linkapediabot',
        'vkShare',
        'Siteimprove.com',
        'BLEXBot\\/',
        'DareBoost',
        'ZuperlistBot\\/',
        'Miniflux\\/',
        'Feedspot',
        'Diffbot\\/',
        'SEOkicks',
        'tracemyfile',
        'Nimbostratus-Bot',
        'zgrab',
        'PR-CY.RU',
        'AdsTxtCrawler',
        'Datafeedwatch',
        'Zabbix',
        'TangibleeBot',
        'google-xrawler',
        'axios',
        'Amazon CloudFront',
        'Pulsepoint',
        'CloudFlare-AlwaysOnline',
        'Google-Structured-Data-Testing-Tool',
        'WordupInfoSearch',
        'WebDataStats',
        'HttpUrlConnection',
        'Seekport Crawler',
        'ZoomBot',
        'VelenPublicWebCrawler',
        'MoodleBot',
        'jpg-newsbot',
        'outbrain',
        'W3C_Validator',
        'Validator\\.nu',
        'W3C-checklink',
        'W3C-mobileOK',
        'W3C_I18n-Checker',
        'FeedValidator',
        'W3C_CSS_Validator',
        'W3C_Unicorn',
        'Google-PhysicalWeb',
        'Blackboard',
        'ICBot\\/',
        'BazQux',
        'Twingly',
        'Rivva',
        'Experibot',
        'awesomecrawler',
        'Dataprovider.com',
        'GroupHigh\\/',
        'theoldreader.com',
        'AnyEvent',
        'Uptimebot\\.org',
        'Nmap Scripting Engine',
        '2ip.ru',
        'Clickagy',
        'Caliperbot',
        'MBCrawler',
        'online-webceo-bot',
        'B2B Bot',
        'AddSearchBot',
        'Google Favicon',
        'HubSpot',
        'HeadlessChrome',
        'CheckMarkNetwork\\/',
        'www\\.uptime\\.com',
        'Streamline3Bot\\/',
        'serpstatbot\\/',
        'MixnodeCache\\/',
        'SimpleScraper',
        'RSSingBot',
        'Jooblebot',
        'fedoraplanet',
        'Friendica',
        'NextCloud',
        'Tiny Tiny RSS',
        'RegionStuttgartBot',
        'Bytespider',
        'Datanyze',
        'Google-Site-Verification',
        'TrendsmapResolver',
        'tweetedtimes',
        'NTENTbot',
        'Gwene',
        'SimplePie',
        'SearchAtlas',
        'Superfeedr',
        'feedbot',
        'UT-Dorkbot',
        'Amazonbot',
        'SerendeputyBot',
        'Eyeotabot',
        'officestorebot',
        'Neticle Crawler',
        'SurdotlyBot',
        'LinkisBot',
        'AwarioSmartBot',
        'AwarioRssBot',
        'RyteBot',
        'FreeWebMonitoring SiteChecker',
        'AspiegelBot',
        'NAVER Blog Rssbot',
        'zenback bot',
        'SentiBot',
        'Domains Project\\/',
        'Pandalytics',
        'VKRobot',
        'bidswitchbot',
        'tigerbot',
        'NIXStatsbot',
        'Atom Feed Robot',
        'Curebot',
        'PagePeeker\\/',
        'Vigil\\/',
        'rssbot\\/',
        'startmebot\\/',
        'JobboerseBot',
        'seewithkids',
        'NINJA bot',
        'Cutbot',
        'BublupBot',
        'BrandONbot',
        'RidderBot',
        'Taboolabot',
        'Dubbotbot',
        'FindITAnswersbot',
        'infoobot',
        'Refindbot',
        'BlogTraffic\\/\\d\\.\\d+ Feed-Fetcher',
        'SeobilityBot',
        'Cincraw',
        'Dragonbot',
        'VoluumDSP-content-bot',
        'FreshRSS',
        'BitBot',
        '^PHP-Curl-Class',
        'Google-Certificates-Bridge',
        'centurybot',
        'Viber',
        'e\\.ventures Investment Crawler',
        'evc-batch',
        'PetalBot',
        'virustotal',
        'S[eE][mM]rushBot',

        // Source: https://www.keycdn.com/blog/web-crawlers
        'Sogou Pic Spider', 'Sogou head spider', 'Sogou web spider', 'Sogou Orion spider', 'Sogou-Test-Spider',
        'Konqueror',
        'coccocbot',
    ];

    /**
     * Returns whether page should be optimized or not based on page url and fastcgi excluded pages merged with option
     *
     * @return bool
     */
    public static function urlIsOptimizable($url = null, $is_admin = false)
    {
        $is_optimizable = self::urlIsOptimizableWithReason($url, $is_admin);

        if (is_bool($is_optimizable)) {
            return $is_optimizable;
        }

        return false;
    }

    /**
     * Returns whether page should be optimized or not based on page url and fastcgi excluded pages merged with option
     * Returns boolean or string. If returns string, it contains the reason why page should not be optimized
     *
     * @return bool|string
     * */
    public static function urlIsOptimizableWithReason($url = null, $is_admin = false)
    {
        if (self::has_donotoptimizepage()) {
            return 'DONOTOPTIMIZEPAGE is set';
        }

        $server_data = $_SERVER;

        if (isset($url)) {
            $url_data = wp_parse_url($url);
            unset($server_data['REQUEST_URI'], $server_data['HTTP_HOST']);

            if (isset($url_data['path'])) {
                $server_data['REQUEST_URI'] = $url_data['path'];
            }

            if (isset($url_data['host'])) {
                $server_data['HTTP_HOST'] = $url_data['host'];
            }
        }

        global $TwoSettings;
        $optimizerDisabledPages = array_filter(
            array_map('trim', explode(',', $TwoSettings->get_settings('two_disabled_speed_optimizer_pages', '')))
        );

        if (!isset($server_data['REQUEST_URI']) || preg_match('~\.xml|\.txt|wp-login\.php|wp-register\.php~', $server_data[ 'REQUEST_URI' ])) {
            return 'Not allowed REQUEST_URI';
        }

        if (!empty($optimizerDisabledPages)) {
            //check excluded pages
            foreach ($optimizerDisabledPages as $optimizerDisabledPage) {
                if (preg_match('~' . $optimizerDisabledPage . '~', $server_data['REQUEST_URI'])) {
                    return 'Page optimization is disabled from TWO options';
                }
            }
        }

        $no_optimize_pages_list = $TwoSettings->get_settings('no_optimize_pages', []);

        if (is_array($no_optimize_pages_list)) {
            foreach ($no_optimize_pages_list as $no_optimize_page) {
                $no_optimize_page = str_replace('/', '', $no_optimize_page);

                if ($no_optimize_page == str_replace('/', '', (isset($server_data['HTTPS']) && $server_data['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $server_data['HTTP_HOST'] . $server_data['REQUEST_URI'])) {
                    return 'Page is in no_optimize_pages list';
                }
            }
        }

        // fix for first request from crawler
        if ((isset($_GET['two_preview']) && $_GET['two_preview'] === '1')) { // phpcs:ignore
            return true;
        }

        // Disable cache for bots.
        if (isset($server_data['HTTP_USER_AGENT']) && preg_match('#(' . implode('|', self::get_non_cached_user_agents()) . ')#', $server_data['HTTP_USER_AGENT'])) {
            // DO NOT CACHE BECAUSE OF HIGH DATABASE LOAD, but serve optimized
            global $disableTwoCacheStructureCache;
            $disableTwoCacheStructureCache = true;
        }

        if (!empty($TwoSettings->get_settings('two_all_pages_are_optimizable', ''))) {
            return true;
        }

        //check explicitly cached pages
        foreach (self::TWO_FASTCGI_CACHED_URLS as $cachedUrl) {
            if (preg_match('~' . $cachedUrl . '~', $server_data['REQUEST_URI'])) {
                return true;
            }
        }

        //check non-cached pages
        $two_non_optimizable_speed_optimizer_pages = array_filter(
            array_map('trim', explode(',', $TwoSettings->get_settings('two_non_optimizable_speed_optimizer_pages', '')))
        );

        foreach ($two_non_optimizable_speed_optimizer_pages as $nonCachedUrl) {
            if (!isset($server_data['REQUEST_URI']) || preg_match('~' . $nonCachedUrl . '~', $server_data['REQUEST_URI'])) {
                return 'URL is in two_non_optimizable_speed_optimizer_pages list';
            }
        }

        if (isset($url) || $is_admin) {
            return true;
        }

        //check non-cached cookies
        if ('on' !== $TwoSettings->get_settings('two_page_cache_user')) {
            foreach (self::TWO_FASTCGI_NON_CACHED_PAGES_IF_COOKIE_EXISTS as $nonCachedCookieName) {
                if (!empty(OptimizerUtils::preg_grep_keys('~' . $nonCachedCookieName . '~', $_COOKIE))) { // phpcs:ignore
                    return 'Page has not allowed cookie: ' . $nonCachedCookieName;
                }
            }
        }

        return true;
    }

    /**
     * Tell if the constant DONOTOPTIMIZEPAGE is set and not overridden.
     * When defined, the page must not be cached.
     *
     * @return bool
     */
    public static function has_donotoptimizepage()
    {
        if (! defined('DONOTOPTIMIZEPAGE') || ! DONOTOPTIMIZEPAGE) {
            return false;
        }

        /*
         * At this point the constant DONOTOPTIMIZEPAGE is set to true.
         * This filter allows to force the page optimization.
         * It prevents conflict with some plugins like Thrive Leads.
         *
         * @since 2.5
         *
         * @param bool $override_donotoptimizepage True will force the page to be cached.
         */
        return ! apply_filters('two_override_donotoptimizepage', false);
    }

    public static function isUrlOptimizableByContent($content)
    {
        if (preg_match('#<script[^>]*src=("|\')([^>]*cdn.ampproject.org[^>]*)("|\')[^>]*>#Usmi', $content)) {
            return 'Do not optimize AMP pages';
        }
        $current_url = get_permalink();
        $current_scheme = wp_parse_url($current_url);

        if (isset($current_scheme['scheme']) && !in_array($current_scheme['scheme'], ['http', 'https'])) {
            return 'Do not optimize ' . $current_scheme . ' pages';
        }

        if (preg_grep('#^Content-Type:(?! *text\/html;)#is', headers_list())) {
            return 'Do not optimize non html pages';
        }

        return true;
    }

    public static function isCriticalSavedInSettings($page_id)
    {
        return 'front_page' == $page_id || false !== strpos($page_id, 'term_') || false !== strpos($page_id, 'user_');
    }

    public static function getPageModeByID($page_id)
    {
        $id = $page_id;
        $type = 'page_';
        $page_mode = false;

        if (false !== strpos($page_id, 'term_')) {
            $id = (int) ltrim($page_id, 'term_');
            $type = 'term_';
        }

        if (false !== strpos($page_id, 'user_')) {
            $id = (int) ltrim($page_id, 'user_');
            $type = 'user_';
        }

        if ('term_' == $type) {
            $page_mode = get_term_meta($id, 'two_mode', true);
        } elseif ('user_' == $type) {
            $page_mode = get_user_meta($id, 'two_mode', true);
        } else {
            $page_mode = get_post_meta($id, 'two_mode', true);
        }

        return [ 'id' => $id, 'type' => $type, 'page_mode' => $page_mode ];
    }

    public static function get_non_cached_user_agents()
    {
        try {
            $crawlerUserAgentsFile = __DIR__ . '/../vendor/monperrus/crawler-user-agents/crawler-user-agents.json';

            if (is_readable($crawlerUserAgentsFile)) {
                $allAgents = json_decode(file_get_contents($crawlerUserAgentsFile), true); //phpcs:ignore WordPressVIPMinimum.Performance.FetchingRemoteData.FileGetContentsUnknown
                $allAgents = array_column($allAgents, 'pattern');
                $allAgents = array_diff($allAgents, self::EXCLUDED_FROM_FULL_LIST_USER_AGENTS);

                if (!empty($allAgents)) {
                    return $allAgents;
                }
            }
        } catch (Exception $exception) {
            return self::TWO_FASTCGI_NON_CACHED_PAGES_IF_USER_AGENT;
        }

        return self::TWO_FASTCGI_NON_CACHED_PAGES_IF_USER_AGENT;
    }
}
