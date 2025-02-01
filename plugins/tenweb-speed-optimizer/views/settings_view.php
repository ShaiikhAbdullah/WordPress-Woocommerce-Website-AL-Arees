<?php
global $TwoSettings;

use TenWebOptimizer\ExcludeJsFromDelay;
use TenWebOptimizer\OptimizerUtils;

$TwoSettings = \TenWebOptimizer\OptimizerSettings::get_instance();

$two_lazyload = $TwoSettings->get_settings('two_lazyload');
$two_add_noscript = $TwoSettings->get_settings('two_add_noscript');
$two_lazyload_slider_images = $TwoSettings->get_settings('two_lazyload_slider_images');
$two_elemrntor_video_iframe = $TwoSettings->get_settings('two_elemrntor_video_iframe');
$two_remove_elementor_lazyload = $TwoSettings->get_settings('two_remove_elementor_lazyload');
$two_bg_lazyload = $TwoSettings->get_settings('two_bg_lazyload');
$two_img_in_viewport_lazyload = $TwoSettings->get_settings('two_img_in_viewport_lazyload');
$two_iframe_lazyload = $TwoSettings->get_settings('two_iframe_lazyload');
$two_delay_iframe_lazyload = $TwoSettings->get_settings('two_delay_iframe_lazyload');
$two_youtube_vimeo_iframe_lazyload = $TwoSettings->get_settings('two_youtube_vimeo_iframe_lazyload');
$two_video_lazyload = $TwoSettings->get_settings('two_video_lazyload');
$two_gzip = $TwoSettings->get_settings('two_gzip');
$two_page_cache = $TwoSettings->get_settings('two_page_cache');
$two_page_cache_user = $TwoSettings->get_settings('two_page_cache_user');
$two_serve_gzip = $TwoSettings->get_settings('two_serve_gzip');
$two_empty_encoding_serve_gzip = $TwoSettings->get_settings('two_empty_encoding_serve_gzip');
$two_enable_plugin_autoupdate = $TwoSettings->get_settings('two_enable_plugin_autoupdate');
$two_minify_html = $TwoSettings->get_settings('two_minify_html');
$two_page_cache_life_time = $TwoSettings->get_settings('two_page_cache_life_time');
$two_disable_jetpack_optimization = $TwoSettings->get_settings('two_disable_jetpack_optimization');
$two_include_inline_css = $TwoSettings->get_settings('two_include_inline_css');
$two_include_inline_js = $TwoSettings->get_settings('two_include_inline_js');
$two_use_extended_exception_list_js = $TwoSettings->get_settings('two_use_extended_exception_list_js');
$two_dequeue_jquery_migrate = $TwoSettings->get_settings('two_dequeue_jquery_migrate');
$two_delayed_js_execution_list = $TwoSettings->get_settings('two_delayed_js_execution_list');
$two_exclude_delay_js = $TwoSettings->get_settings('two_exclude_delay_js');
$two_delay_custom_js_new = $TwoSettings->get_settings('two_delay_custom_js_new');
$two_delay_js_execution = $TwoSettings->get_settings('two_delay_js_execution');
$two_delay_all_js_execution = $TwoSettings->get_settings('two_delay_all_js_execution');
$two_timeout_js_load = $TwoSettings->get_settings('two_timeout_js_load');
$two_load_excluded_js_via_worker = $TwoSettings->get_settings('two_load_excluded_js_via_worker');
$two_load_excluded_js_normally = $TwoSettings->get_settings('two_load_excluded_js_normally');
$two_events_after_load = $TwoSettings->get_settings('two_events_after_load');
$two_delayed_js_load_libs_first = $TwoSettings->get_settings('two_delayed_js_load_libs_first');
$two_exclude_js = $TwoSettings->get_settings('two_exclude_js');
$two_exclude_css = $TwoSettings->get_settings('two_exclude_css');
$two_async_css = $TwoSettings->get_settings('two_async_css');
$two_async_all = $TwoSettings->get_settings('two_async_all');
$two_disable_wp_dashicons = $TwoSettings->get_settings('two_disable_wp_dashicons');
$two_add_overflow_to_page = $TwoSettings->get_settings('two_add_overflow_to_page');
$two_disable_css = $TwoSettings->get_settings('two_disable_css');
$two_generate_ccss_on_load = $TwoSettings->get_settings('two_generate_ccss_on_load');
$two_fonts_to_preload = $TwoSettings->get_settings('two_fonts_to_preload');
$two_fonts_to_preconnect = $TwoSettings->get_settings('two_fonts_to_preconnect');
$two_disabled_speed_optimizer_pages = $TwoSettings->get_settings('two_disabled_speed_optimizer_pages');
$two_disabled_delay_all_js_pages = $TwoSettings->get_settings('two_disabled_delay_all_js_pages');
$two_exclude_lazyload = $TwoSettings->get_settings('two_exclude_lazyload');
$two_do_not_optimize_images = $TwoSettings->get_settings('two_do_not_optimize_images');
$two_enable_use_srcset = $TwoSettings->get_settings('two_enable_use_srcset');
$two_enable_nginx_webp_delivery = $TwoSettings->get_settings('two_enable_nginx_webp_delivery');
$two_enable_htaccess_webp_delivery = $TwoSettings->get_settings('two_enable_htaccess_webp_delivery');
$two_enable_picture_webp_delivery = $TwoSettings->get_settings('two_enable_picture_webp_delivery');
$two_disable_nginx_webp_delivery_option = !defined('IOWD_PREFIX') ? ' disabled' : '';
$two_exclude_images_for_optimize = $TwoSettings->get_settings('two_exclude_images_for_optimize');
$two_async_font = $TwoSettings->get_settings('two_async_font');
$two_merge_google_font_faces = $TwoSettings->get_settings('two_merge_google_font_faces');
$two_load_fonts_via_webfont = $TwoSettings->get_settings('two_load_fonts_via_webfont');
$two_serve_optimized_bg_image = $TwoSettings->get_settings('two_serve_optimized_bg_image');
$two_files_cache = $TwoSettings->get_settings('two_files_cache');
$two_enable_htaccess_caching_headers = $TwoSettings->get_settings('two_enable_htaccess_caching_headers');
$two_test_mode = $TwoSettings->get_settings('two_test_mode');
$two_all_pages_are_optimizable = $TwoSettings->get_settings('two_all_pages_are_optimizable');
$two_minify_js = $TwoSettings->get_settings('two_minify_js');
$two_minify_css = $TwoSettings->get_settings('two_minify_css');
$lazy_load_type = $TwoSettings->get_settings('lazy_load_type');

$two_async_page = $TwoSettings->get_settings('two_async_page');
$two_disable_page = $TwoSettings->get_settings('two_disable_page');

$two_change_minify = $TwoSettings->get_settings('two_change_minify');

$two_aggregate_js = $TwoSettings->get_settings('two_aggregate_js');
$two_aggregate_css = $TwoSettings->get_settings('two_aggregate_css');
$two_delay_js_exclusions = $TwoSettings->get_settings('two_delay_js_exclusions');
$cloudflare_cache_status = $TwoSettings->get_settings('cloudflare_cache_status');
$two_non_optimizable_speed_optimizer_pages = $TwoSettings->get_settings('two_non_optimizable_speed_optimizer_pages');
$two_disconnect_nonce = wp_create_nonce('two_disconnect_nonce');

OptimizerUtils::two_critical_status();

$table_data = '';

if (isset($two_async_page) && is_array($two_async_page) && $two_async_all !== 'on') {
    foreach ($two_async_page as $key => $val) {
        if (!empty($val)) {
            $two_async_page_css = explode(',', $val);
            $two_async_page_css = array_filter($two_async_page_css);

            foreach ($two_async_page_css as $name) {
                $table_data .= "<tr>
                          <td class='two_css_name'>" . $name . "</td>
                          <td class='two_url'>" . $key . "</td>
                          <td class='two_load_type'>Async</td>
                          <td>
                             <span data-task='edit' class='two_edit_element dashicons dashicons-edit'></span>
                             <span data-task='duplicate' class='two_duplicate_element dashicons dashicons-admin-page'></span>
                             <span class='two_delete_element dashicons dashicons-trash'></span>
                          </td>
                      </tr>";
            }
        }
    }
}

if (isset($two_disable_page) && is_array($two_disable_page)) {
    foreach ($two_disable_page as $key => $val) {
        if (!empty($val)) {
            $page_disable_page_css = explode(',', $val);
            $page_disable_page_css = array_filter($page_disable_page_css);

            foreach ($page_disable_page_css as $name) {
                $table_data .= "<tr>
                          <td class='two_css_name'>" . $name . "</td>
                          <td class='two_url'>" . $key . "</td>
                          <td class='two_load_type'>Disabled</td>
                          <td>
                            <span data-task='edit' class='two_edit_element dashicons dashicons-edit-large '></span>
                            <span data-task='duplicate' class='two_duplicate_element dashicons dashicons-admin-page'></span>
                            <span class='two_delete_element dashicons dashicons-trash'></span>
                          </td>
                      </tr>";
            }
        }
    }
}

$exclude_lazyload = '';

if ($two_lazyload == false && $two_bg_lazyload == false && $two_iframe_lazyload == false && $two_video_lazyload == false) {
    $exclude_lazyload = 'display:none;';
}
$exclude_images_for_optimize = '';

if ($two_do_not_optimize_images == true) {
    $exclude_images_for_optimize = 'display:none;';
}
$add_noscript_style = '';

if ($two_lazyload == false) {
    $add_noscript_style = 'display:none;';
}
$lazyload_check = ($two_lazyload == 'on') ? 'checked' : '';
$add_noscript_check = ($two_add_noscript == 'on') ? 'checked' : '';
$two_lazyload_slider_images_check = ($two_lazyload_slider_images == 'on') ? 'checked' : '';
$two_serve_optimized_bg_image = ($two_serve_optimized_bg_image == 'on') ? 'checked' : '';
$two_files_cache = ($two_files_cache == 'on') ? 'checked' : '';
$two_enable_htaccess_caching_headers = ($two_enable_htaccess_caching_headers == 'on') ? 'checked' : '';
$two_test_mode = ($two_test_mode == 'on') ? 'checked' : '';
$cloudflare_cache_status = ($cloudflare_cache_status == 'on') ? 'checked' : '';
$two_all_pages_are_optimizable = ($two_all_pages_are_optimizable == 'on') ? 'checked' : '';
$two_do_not_optimize_images = ($two_do_not_optimize_images == 'on') ? 'checked' : '';
$two_enable_use_srcset = ($two_enable_use_srcset == 'on') ? 'checked' : '';
$two_enable_nginx_webp_delivery = ($two_enable_nginx_webp_delivery == 'on') ? 'checked' : '';
$two_enable_htaccess_webp_delivery = ($two_enable_htaccess_webp_delivery == 'on') ? 'checked' : '';
$two_enable_picture_webp_delivery = ($two_enable_picture_webp_delivery == 'on') ? 'checked' : '';
$lazyload_bg_check = ($two_bg_lazyload == 'on') ? 'checked' : '';
$two_img_in_viewport_lazyload_check = ($two_img_in_viewport_lazyload == 'on') ? 'checked' : '';
$two_delay_js_execution_check = ($two_delay_js_execution == 'on') ? 'checked' : '';
$two_delay_all_js_execution_check = ($two_delay_all_js_execution == 'on') ? 'checked' : '';
$two_timeout_js_load_check = ($two_timeout_js_load == 'on') ? 'checked' : '';
$two_load_excluded_js_via_worker_check = ($two_load_excluded_js_via_worker == 'on') ? 'checked' : '';
$two_events_after_load_check = is_array($two_events_after_load) ? $two_events_after_load : [];
$two_delayed_js_load_libs_first = ($two_delayed_js_load_libs_first == 'on') ? 'checked' : '';
$lazyload_iframe_check = ($two_iframe_lazyload == 'on') ? 'checked' : '';
$delay_lazyload_iframe_check = ($two_delay_iframe_lazyload == 'on') ? 'checked' : '';
$two_elemrntor_video_iframe_check = ($two_elemrntor_video_iframe == 'on') ? 'checked' : '';
$two_remove_elementor_lazyload_check = ($two_remove_elementor_lazyload == 'on') ? 'checked' : '';
$lazyload_youtube_vimeo_iframe_check = ($two_youtube_vimeo_iframe_lazyload == 'on') ? 'checked' : '';
$lazyload_video_check = ($two_video_lazyload == 'on') ? 'checked' : '';
$aggregate_js_check = ($two_aggregate_js == 'on') ? 'checked' : '';
$aggregate_css_check = ($two_aggregate_css == 'on') ? 'checked' : '';
$two_gzip_check = ($two_gzip == 'on') ? 'checked' : '';
$two_page_cache_check = ($two_page_cache == 'on') ? 'checked' : '';
$two_page_cache_user_check = ($two_page_cache_user == 'on') ? 'checked' : '';
$two_serve_gzip_check = ($two_serve_gzip == 'on') ? 'checked' : '';
$two_empty_encoding_serve_gzip_check = ($two_empty_encoding_serve_gzip == 'on') ? 'checked' : '';
$two_enable_plugin_autoupdate_check = ($two_enable_plugin_autoupdate == 'on') ? 'checked' : '';
$two_minify_html_check = ($two_minify_html == 'on') ? 'checked' : '';
$two_disable_jetpack_optimization_check = ($two_disable_jetpack_optimization == 'on') ? 'checked' : '';
$two_generate_ccss_on_load_check = is_array($two_generate_ccss_on_load) ? $two_generate_ccss_on_load : [];
$two_include_inline_css = ($two_include_inline_css == 'on') ? 'checked' : '';
$two_include_inline_js_check = ($two_include_inline_js == 'on') ? 'checked' : '';
$two_use_extended_exception_list_js = ($two_use_extended_exception_list_js == 'on') ? 'checked' : '';
$two_dequeue_jquery_migrate = ($two_dequeue_jquery_migrate == 'on') ? 'checked' : '';
$two_minify_css = ($two_minify_css == 'on') ? 'checked' : '';
$two_disable_wp_dashicons = ($two_disable_wp_dashicons == 'on') ? 'checked' : '';
$two_minify_js = ($two_minify_js == 'on') ? 'checked' : '';
$two_async_font = ($two_async_font == 'on') ? 'checked' : '';
$two_merge_google_font_faces_check = ($two_merge_google_font_faces == 'on') ? 'checked' : '';
$two_load_fonts_via_webfont = ($two_load_fonts_via_webfont == 'on') ? 'checked' : '';
$two_flow_status = get_option('two_flow_status');
$valid_permalink_for_page_cache = $GLOBALS['wp_rewrite']->using_permalinks();

if ($two_change_minify === false) {
    $two_change_minify = 'JSMin';
}

if ($lazy_load_type === false) {
    $lazy_load_type = 'vanilla';
}

if ($two_async_all === 'on') {
    $disable_async = 'display:none;';
    $two_async_all = 'checked';
} else {
    $disable_async = '';
    $two_async_all = '';
}

$exclusion_data = ExcludeJsFromDelay::get_exclusion_data();

$connection_link = \TenWebOptimizer\OptimizerUtils::get_tenweb_connection_link();
$disconnect_link = $return_url = get_admin_url() . 'admin.php?page=two_settings_page&two_disconnect=1&nonce=' . $two_disconnect_nonce;

$show_page_cache_life_time = ($two_page_cache_check === 'checked' && $valid_permalink_for_page_cache) ? '' : 'display:none';
$show_bg_lazy_load = ($lazy_load_type === 'vanilla') ? '' : 'display:none';

?>
<div class="two_settings_container" dir="ltr" xmlns="http://www.w3.org/1999/html">
    <div class="two_settings wd-box-section">
        <div class="wd-box-title">
            <strong><?php _e(esc_html(TWO_SO_ORGANIZATION_NAME) . ' Booster', 'tenweb-speed-optimizer'); ?></strong> <small>v<?php echo esc_html(TENWEB_SO_VERSION); ?></small><br>
            <strong><?php $twoMode = get_option('two_default_mode', OptimizerUtils::MODES['extreme']);
            echo is_array($twoMode) && !empty($twoMode['mode']) ? esc_html(ucfirst($twoMode['mode'])) : 'No'; ?></strong> mode
        </div>


        <div class="two_tab_menus">
            <span data-tab="general" class="two_tab_menu active">General</span>
            <span data-tab="css" class="two_tab_menu">Css</span>
            <span data-tab="js" class="two_tab_menu">Js</span>
            <span data-tab="lazy" class="two_tab_menu">Lazy load</span>
            <span data-tab="image" class="two_tab_menu">Images</span>
            <span data-tab="page_css" class="two_tab_menu">Css in specific pages</span>
            <span data-tab="critical_css" class="two_tab_menu">Critical Css</span>
            <span data-tab="export_import" class="two_tab_menu">Export/import</span>
            <span data-tab="two_logs" class="two_tab_menu">Logs</span>
            <span data-tab="non_optimizable_pages" class="two_tab_menu">Non Optimizable Pages</span>
        </div>
        <form class="two_settings_form" action="" method="post">
            <div class="two_settings_tab two_tab_general active">
              <?php if (!\TenWebOptimizer\OptimizerUtils::check_if_hosted_website()) { ?>
                <div class="two_settings_option">
                    <p><b style="font-size: 16px;">Connected to <?php echo esc_html(TWO_SO_ORGANIZATION_NAME); ?>:
                      <?php
                      if (\Tenweb_Authorization\Login::get_instance()->check_logged_in()) {
                          echo 'Yes';
                      } else {
                          echo 'No';
                      }
                      ?>
                        </b></p>
                    <a class="button" href="<?php echo esc_url($connection_link); ?>">Connect</a>
                    <a class="button" href="<?php echo esc_url($disconnect_link); ?>">Disconnect</a>
                    <?php if ($two_flow_status == '3' || $two_flow_status == '4') { ?>
                        <span data-flow_status="<?php echo esc_attr($two_flow_status); ?>" class="button two_finish_flow" href="<?php echo esc_url($disconnect_link); ?>">Finish flow</span>
                    <?php }?>
                </div>
                <?php } ?>
              <div class="two_settings_option">
                <input <?php echo esc_html($two_test_mode); ?> type="checkbox" name="two_test_mode"
                                                                 id="two_test_mode">
                <label for="two_test_mode"
                       class="wd-label"><?php _e('Enable test mode', 'tenweb-speed-optimizer'); ?></label>
              </div>
              <p class="description"><?php _e('Enable test mode to disable optimization for live site. Add ?twbooster=1 to urls to load optimized pages.', 'tenweb-speed-optimizer'); ?></p>


                <div class="two_settings_option">
                    <input <?php echo esc_html($cloudflare_cache_status); ?> type="checkbox" name="cloudflare_cache_status"
                                                                     id="cloudflare_cache_status">
                    <label for="cloudflare_cache_status"
                           class="wd-label"><?php _e('Cloudflare cache status', 'tenweb-speed-optimizer'); ?></label>
                </div>



                <div class="two_settings_option">
                    <input <?php echo esc_html($two_files_cache); ?> type="checkbox" name="two_files_cache"
                                                           id="two_files_cache">
                    <label for="two_files_cache"
                           class="wd-label"><?php _e('Cache generated static files', 'tenweb-speed-optimizer'); ?></label>
                </div>
                <p class="description"><?php _e('Enable this option to reuse generated static files for every request and speed up page loading speed', 'tenweb-speed-optimizer'); ?></p>

                <?php if (!TENWEB_SO_HOSTED_ON_NGINX) { ?>

                    <div class="two_settings_option">
                        <input <?php echo esc_html($two_enable_htaccess_caching_headers); ?> type="checkbox" name="two_enable_htaccess_caching_headers"
                                                                           id="two_enable_htaccess_caching_headers">
                        <label for="two_enable_htaccess_caching_headers"
                               class="wd-label"><?php _e('Write caching headers in .htaccess', 'tenweb-speed-optimizer'); ?></label>
                    </div>
                    <p class="description"><?php _e('Enable this option to write recommended caching headers in .htaccess file', 'tenweb-speed-optimizer'); ?></p>

                <?php } ?>
                <div class="two_settings_option">
                    <input <?php echo esc_html($two_page_cache_check); ?> type="checkbox" <?php echo (!$valid_permalink_for_page_cache) ? 'disabled' : ''; ?> name="two_page_cache" id="two_page_cache">
                    <label for="two_page_cache" class="wd-label"><?php _e('Enable page cache', 'tenweb-speed-optimizer'); ?></label>

                <p class="description"><?php
                  if (!$valid_permalink_for_page_cache) {
                      echo "<span class='warning'>";
                      echo wp_kses_post(sprintf(
                          __('This option requires a custom permalink structure to work properly. %1$sGo to permalinks settings to change it.%2$s', 'tenweb-speed-optimizer'),
                          '<a href="' . esc_url(admin_url('options-permalink.php')) . '">',
                          '</a>'
                      ));
                      echo '</span> <br/>';
                  }
                  _e('Enable this option to reuse generated static html files for every request and speed up page loading speed.', 'tenweb-speed-optimizer');
                  ?></p>
                </div>
                <div class="two_settings_option" style="<?php echo esc_attr($show_page_cache_life_time); ?>">
                    <input <?php echo esc_html($two_page_cache_user_check); ?> type="checkbox" name="two_page_cache_user" id="two_page_cache_user">
                    <label for="two_page_cache_user" class="wd-label"><?php _e('Enable page cache for logged in users', 'tenweb-speed-optimizer'); ?></label>

                <p class="description"><?php _e('Enable this option to reuse generated static html files for every request and speed up page loading speed.', 'tenweb-speed-optimizer'); ?></p>
                </div>
                    <div class="two_settings_option" style="<?php echo esc_attr($show_page_cache_life_time); ?>">
                        <label for="two_page_cache_life_time" class="wd-label"><?php _e('Page cache life time', 'tenweb-speed-optimizer'); ?></label>
                        <input type="number" name="two_page_cache_life_time" id="two_page_cache_life_time" value="<?php echo esc_attr($two_page_cache_life_time); ?>">
                        <p class="description"><?php _e('Page cache life time in seconds. The default value is 7 days.', 'tenweb-speed-optimizer'); ?></p>
                    </div>
                <div class="two_settings_option">
                    <input <?php echo esc_html($two_enable_plugin_autoupdate_check); ?> type="checkbox" name="two_enable_plugin_autoupdate" id="two_enable_plugin_autoupdate">
                    <label for="two_enable_plugin_autoupdate" class="wd-label"><?php _e('Enable plugin autoupdate', 'tenweb-speed-optimizer'); ?></label>
                    <p class="description"><?php _e('When checked, Wordpress will autoupdate this plugin.', 'tenweb-speed-optimizer'); ?></p>
                </div>
                <div class="two_settings_option">
                    <input <?php echo esc_html($two_serve_gzip_check); ?> type="checkbox" name="two_serve_gzip" id="two_serve_gzip">
                    <label for="two_serve_gzip" class="wd-label"><?php _e('Serve compressed HTML', 'tenweb-speed-optimizer'); ?></label>
                </div>
                <div class="two_settings_option two_empty_encoding_serve_gzip">
                    <input <?php echo esc_html($two_empty_encoding_serve_gzip_check); ?> type="checkbox" name="two_empty_encoding_serve_gzip" id="two_empty_encoding_serve_gzip">
                    <label for="two_empty_encoding_serve_gzip" class="wd-label"><?php _e('Serve compressed HTML in any case', 'tenweb-speed-optimizer'); ?></label>
                    <p class="description"><?php _e('Serve compressed HTML when no "Accept-Encoding" header is provided', 'tenweb-speed-optimizer'); ?></p>
                </div>
                <div class="two_settings_option">
                  <input <?php echo esc_html($two_minify_html_check); ?> type="checkbox" name="two_minify_html"
                                                                     id="two_minify_html">
                  <label for="two_minify_html"
                         class="wd-label"><?php _e('Minify HTML', 'tenweb-speed-optimizer'); ?></label>
                </div>
                <p class="description"><?php _e('Enable this option to serve minified HTML.', 'tenweb-speed-optimizer'); ?></p>
                <div class="two_settings_option">
                    <input <?php echo esc_html($two_all_pages_are_optimizable); ?> type="checkbox" name="two_all_pages_are_optimizable"
                                                           id="two_all_pages_are_optimizable">
                    <label for="two_all_pages_are_optimizable"
                           class="wd-label"><?php _e('Run Optimizer on every page', 'tenweb-speed-optimizer'); ?></label>
                </div>
                <p class="description"><?php _e('Enable this option to run optimizer on every page, except excluded.', 'tenweb-speed-optimizer'); ?></p>

                <div class="two_settings_option">
                    <input <?php echo esc_html($two_async_font); ?> type="checkbox" name="two_async_font" id="two_async_font">
                    <label for="two_async_font" class="wd-label"><?php _e('Font Swap', 'tenweb-speed-optimizer'); ?></label>
                </div>
                <p class="description"><?php _e('Displays data with generic font while your font style is being fetched.', 'tenweb-speed-optimizer'); ?></p>
                <div class="two_settings_option">
                    <input <?php echo esc_html($two_merge_google_font_faces_check); ?> type="checkbox" name="two_merge_google_font_faces" id="two_merge_google_font_faces">
                    <label for="two_merge_google_font_faces" class="wd-label"><?php _e('Merge google font faces', 'tenweb-speed-optimizer'); ?></label>
                </div>
                <p class="description"><?php _e('Merge Google font faces before connecting', 'tenweb-speed-optimizer'); ?></p>
                <div class="two_settings_option">
                    <input <?php echo esc_html($two_load_fonts_via_webfont); ?> type="checkbox" name="two_load_fonts_via_webfont"
                                                                      id="two_load_fonts_via_webfont">
                    <label for="two_load_fonts_via_webfont"
                           class="wd-label"><?php _e('Use WebFont', 'tenweb-speed-optimizer'); ?></label>
                </div>
                <p class="description"><?php _e('Load fonts with WebFont.', 'tenweb-speed-optimizer'); ?></p>
                <?php
                /** this option is not working properly so it is disabled */
                /* @noinspection SuspiciousBinaryOperationInspection */
                if (!TENWEB_SO_HOSTED_ON_10WEB && false) {
                    ?>
                    <div class="two_settings_option">
                        <input <?php echo esc_html($two_gzip_check); ?> type="checkbox" name="two_gzip" id="gzip_css_js">
                        <label for="gzip_css_js" class="wd-label"><?php _e('Gzip CSS and JS', 'tenweb-speed-optimizer'); ?></label>
                    </div>
                    <p class="description"><?php _e('Compress components with gzip to save time and bandwith.', 'tenweb-speed-optimizer'); ?></p>
                    <?php
                }
                ?>
                <?php
                if (defined('JETPACK__VERSION')) {
                    ?>
                    <div class="two_settings_option">
                        <input <?php echo esc_html($two_disable_jetpack_optimization_check); ?> type="checkbox" name="two_disable_jetpack_optimization" id="disable_jetpack_optimization">
                        <label for="disable_jetpack_optimization" class="wd-label"><?php _e('Disable Jetpack optimization', 'tenweb-speed-optimizer'); ?></label>
                    </div>
                    <p class="description"><?php _e('This option will disable Performance & speed options of Jetpack plugin.', 'tenweb-speed-optimizer'); ?></p>
                    <?php
                }
                ?>
                <div class="two_settings_option two_generate_ccss_on_load">
                    <label for="two_generate_ccss_on_load" class="wd-label"><?php _e('Generate CCSS on load', 'tenweb-speed-optimizer'); ?></label>
                    <select name="two_generate_ccss_on_load" id="two_generate_ccss_on_load" multiple="multiple" placeholder="Choose page types" data-allow-clear="1">
                        <option <?php echo (in_array('page', $two_generate_ccss_on_load_check)) ? 'selected' : ''; ?> value="page">Pages</option>
                        <option <?php echo (in_array('post', $two_generate_ccss_on_load_check)) ? 'selected' : ''; ?> value="post">Posts</option>
                        <option <?php echo (in_array('taxonomy', $two_generate_ccss_on_load_check)) ? 'selected' : ''; ?> value="taxonomy">Taxonomies</option>
                    </select>
                    <p class="description"><?php _e('Post Types where CCSS will be generated on first load in incognito.', 'tenweb-speed-optimizer'); ?></p>
                </div>
                <div class="two_settings_option">
                    <label for="two_fonts_to_preload"
                           class="wd-label"><?php _e('Links that Need to be Preloaded', 'tenweb-speed-optimizer'); ?></label>
                    <textarea style="width: 100%" rows="6" name="two_fonts_to_preload"
                              id="two_fonts_to_preload"><?php echo isset($two_fonts_to_preload) ? esc_textarea($two_fonts_to_preload) : ''; ?></textarea>
                    <p class="description"><?php _e('List of links that you can preload to increase site speed', 'tenweb-speed-optimizer'); ?></p>
                </div>
                <div class="two_settings_option">
                    <label for="two_fonts_to_preconnect"
                           class="wd-label"><?php _e('Links that Need to be Preconnected', 'tenweb-speed-optimizer'); ?></label>
                    <textarea style="width: 100%" rows="6" name="two_fonts_to_preconnect"
                              id="two_fonts_to_preconnect"><?php echo isset($two_fonts_to_preconnect) ? esc_textarea($two_fonts_to_preconnect) : ''; ?></textarea>
                    <p class="description"><?php _e('List of links that you can preconnect to increase site speed', 'tenweb-speed-optimizer'); ?></p>
                </div>
                <div class="two_settings_option">
                    <label for="two_disabled_speed_optimizer_pages"
                           class="wd-label"><?php _e('Pages Where ' . esc_html(TWO_SO_ORGANIZATION_NAME) . ' Booster is Disabled', 'tenweb-speed-optimizer'); ?></label>
                    <textarea style="width: 100%" rows="6" name="two_disabled_speed_optimizer_pages"
                              id="two_disabled_speed_optimizer_pages"><?php echo isset($two_disabled_speed_optimizer_pages) ? esc_textarea($two_disabled_speed_optimizer_pages) : ''; ?></textarea>
                    <p class="description">
                      <?php _e('Paths of web pages where ' . esc_html(TWO_SO_ORGANIZATION_NAME) . ' Booster will not work. For making all pages optimizable set two_all_pages_are_optimizable option to true.', 'tenweb-speed-optimizer'); ?><br />
                      "^/[^.]+$" use this expression to exclude all pages except the homepage (w/o quotes)<br />
                      "^/$" use this expression to exclude homepage (w/o quotes)<br />
                      "^((?!red|green|blue).)*$" use this expression to exclude every string that not contains red, green or blue words (w/o quotes)<br />
                      add any subdirectory before the slash in expression (e.g. /wordpress/) if the site is in subdirectory.
                    </p>
                </div>
                <div class="two_settings_option">
                    <select name="two_change_minify" id="two_change_minify">
                        <option <?php echo ($two_change_minify === 'JSMin') ? 'selected' : ''; ?> value="JSMin">JSMin</option>
                        <option <?php echo ($two_change_minify === 'Minify') ? 'selected' : ''; ?> value="Minify">Minify</option>
                    </select>
                    <label for="lazy_load_type" class="wd-label"><?php _e('Minify library type', 'tenweb-speed-optimizer'); ?></label>
                </div>
            </div>
            <div class="two_settings_tab two_tab_css">
                <div class="two_settings_option">
                    <input <?php echo esc_html($aggregate_css_check); ?> type="checkbox" name="two_aggregate_css"
                                                               id="enable_css_aggregate">
                    <label for="enable_css_aggregate"
                           class="wd-label"><?php _e('Aggregate CSS files', 'tenweb-speed-optimizer'); ?></label>
                    <p class="description"><?php _e('Enable this option to aggregate CSS files', 'tenweb-speed-optimizer'); ?></p>
                </div>
                <div class="two_settings_option two_include_inline_css" <?php if ($two_aggregate_css !== 'on') {
                    echo 'style="display:none"';
                } ?>>
                    <input <?php echo esc_html($two_include_inline_css); ?> type="checkbox" name="two_include_inline_css"
                                                                  id="two_include_inline_css">
                    <label for="two_include_inline_css"
                           class="wd-label"><?php _e('Include inline CSS', 'tenweb-speed-optimizer'); ?></label>
                    <p class="description"><?php _e('Enable this checkbox to compress the internal CSS of your web pages.', 'tenweb-speed-optimizer'); ?></p>
                </div>
                <div class="two_settings_option">
                    <input <?php echo esc_html($two_minify_css); ?> type="checkbox" name="two_minify_css"
                                                          id="two_minify_css">
                    <label for="two_minify_css"
                           class="wd-label"><?php _e('Minify CSS files', 'tenweb-speed-optimizer'); ?></label>
                    <p class="description"><?php _e('Enable this checkbox to minify CSS files', 'tenweb-speed-optimizer'); ?></p>
                </div>
                <div class="two_settings_option">
                    <label for="two_exclude_css"
                           class="wd-label"><?php _e(esc_html($TwoSettings->settings_names['two_exclude_css']['title']), 'tenweb-speed-optimizer'); ?></label>
                    <textarea style="width: 100%" name="two_exclude_css"
                              id="two_exclude_css"><?php echo isset($two_exclude_css) ? esc_textarea($two_exclude_css) : ''; ?></textarea>
                    <p class="description"><?php _e(esc_html($TwoSettings->settings_names['two_exclude_css']['description']), 'tenweb-speed-optimizer'); ?></p>
                </div>
                <div class="two_settings_option">
                    <input <?php echo esc_html($two_async_all); ?> type="checkbox" name="two_async_all" id="two_async_all">
                    <label for="two_async_all" class="wd-label"><?php _e('Async All CSS', 'tenweb-speed-optimizer'); ?></label>
                    <p class="description"><?php _e('Enable this checkbox to async all css files.', 'tenweb-speed-optimizer'); ?></p>
                </div>
                <div class="two_settings_option">
                    <input <?php echo esc_html($two_disable_wp_dashicons); ?> type="checkbox" name="two_disable_wp_dashicons" id="two_disable_wp_dashicons">
                    <label for="two_disable_wp_dashicons" class="wd-label"><?php _e('Disable dashicons', 'tenweb-speed-optimizer'); ?></label>
                    <p class="description"><?php _e('Enable this checkbox to disable dashicons.css .', 'tenweb-speed-optimizer'); ?></p>
                </div>
                <div class="two_settings_option">
                    <select name="two_add_overflow_to_page" id="two_add_overflow_to_page">
                        <option <?php echo ($two_add_overflow_to_page === 'off') ? 'selected' : ''; ?> value="off">Disabled</option>
                        <option <?php echo ($two_add_overflow_to_page === 'body') ? 'selected' : ''; ?> value="body">On body</option>
                        <option <?php echo ($two_add_overflow_to_page === 'html') ? 'selected' : ''; ?> value="html">On HTML</option>
                        <option <?php echo ($two_add_overflow_to_page === 'body, html') ? 'selected' : ''; ?> value="body, html">On body and HTML</option>
                    </select>
                    <label for="two_add_overflow_to_page" class="wd-label"><?php _e(esc_html($TwoSettings->settings_names['two_add_overflow_to_page']['title']), 'tenweb-speed-optimizer'); ?></label>
                    <p class="description"><?php _e(esc_html($TwoSettings->settings_names['two_add_overflow_to_page']['description']), 'tenweb-speed-optimizer'); ?></p>
                </div>
                <div class="two_settings_option two_async_css" style="<?php echo esc_attr($disable_async); ?>">
                    <label for="two_async_css" class="wd-label"><?php _e('Async CSS Files', 'tenweb-speed-optimizer'); ?></label>
                    <textarea style="width: 100%" rows="6" name="two_async_css"
                              id="two_async_css"><?php echo isset($two_async_css) ? esc_textarea($two_async_css) : ''; ?></textarea>
                    <p class="description"><?php _e('Mention the handles of the CSS files to load after the main site content is loaded. This is helpful when there are CSS files that can slow down the website.', 'tenweb-speed-optimizer'); ?></p>
                </div>
                <div class="two_settings_option">
                    <label for="two_disable_css"
                           class="wd-label"><?php _e('Disable CSS Files', 'tenweb-speed-optimizer'); ?></label>
                    <textarea style="width: 100%" rows="6" name="two_disable_css"
                              id="two_disable_css"><?php echo isset($two_disable_css) ? esc_textarea($two_disable_css) : ''; ?></textarea>
                    <p class="description"><?php _e('Disabled CSS Files Use this option to specify the CSS files that are not used and should be disabled from all pages of your website.', 'tenweb-speed-optimizer'); ?></p>
                </div>
            </div>
            <div class="two_settings_tab two_tab_js">
                <div class="two_settings_option two_delay_all_js_execution">
                    <input <?php echo esc_html($two_delay_all_js_execution_check); ?> type="checkbox" name="two_delay_all_js_execution"
                                                                            id="two_delay_all_js_execution">
                    <label for="two_delay_all_js_execution"
                           class="wd-label"><?php _e(esc_html($TwoSettings->settings_names['two_delay_all_js_execution']['title']), 'tenweb-speed-optimizer'); ?></label>
                    <p class="description"><?php _e(esc_html($TwoSettings->settings_names['two_delay_all_js_execution']['description']), 'tenweb-speed-optimizer'); ?></p>
                </div>

                <div class="two_settings_option two_timeout_js_load" <?php if ($two_delay_all_js_execution != 'on') {
                    echo 'style="display:none"';
                } ?>>
                    <input <?php echo esc_html($two_timeout_js_load_check); ?> type="checkbox" name="two_timeout_js_load"
                                                                            id="two_timeout_js_load">
                    <label for="two_timeout_js_load"
                           class="wd-label"><?php _e('Timeout to js load', 'tenweb-speed-optimizer'); ?></label>
                    <p class="description"><?php _e('timeout to js load', 'tenweb-speed-optimizer'); ?></p>
                </div>
                <div class="two_settings_option two_load_excluded_js_via_worker" <?php if ($two_delay_all_js_execution != 'on') {
                    echo 'style="display:none"';
                } ?>>
                    <input <?php echo esc_html($two_load_excluded_js_via_worker_check); ?> type="checkbox" name="two_load_excluded_js_via_worker"
                                                                            id="two_load_excluded_js_via_worker">
                    <label for="two_load_excluded_js_via_worker"
                           class="wd-label"><?php _e('Load excluded JS files via worker', 'tenweb-speed-optimizer'); ?></label>
                    <p class="description"><?php _e('Load excluded JS files via worker and connect them immediately.', 'tenweb-speed-optimizer'); ?></p>
                </div>
                <div class="two_settings_option two_load_excluded_js_normally" <?php if ($two_load_excluded_js_via_worker != 'on' || ($two_load_excluded_js_via_worker == 'on' && $two_delay_all_js_execution != 'on')) {
                    echo 'style="display:none"';
                } ?>>
                    <label for="two_load_excluded_js_normally"
                           class="wd-label"><?php _e('Excluded JS files via worker load', 'tenweb-speed-optimizer'); ?></label>
                    <textarea style="width: 100%" name="two_load_excluded_js_normally"
                              id="two_load_excluded_js_normally"><?php echo isset($two_load_excluded_js_normally) ? esc_textarea($two_load_excluded_js_normally) : ''; ?></textarea>
                    <p class="description"><?php _e('Use this textbox to specify JavaScript files and exclude JS files via worker load.', 'tenweb-speed-optimizer'); ?></p>
                </div>
                <div class="two_settings_option two_events_after_load" <?php if ($two_delay_all_js_execution != 'on') {
                    echo 'style="display:none"';
                } ?>>
                    <label for="two_events_after_load" class="wd-label"><?php _e('Events After Load', 'tenweb-speed-optimizer'); ?></label>
                    <select name="two_events_after_load" id="two_events_after_load" multiple="multiple" placeholder="Choose Event" data-allow-clear="1">
                        <option <?php echo (in_array('DOMContentLoaded', $two_events_after_load_check)) ? 'selected' : ''; ?> value="DOMContentLoaded">DOMContentLoaded</option>
                        <option <?php echo (in_array('Load', $two_events_after_load_check)) ? 'selected' : ''; ?> value="Load">Load</option>
                        <option <?php echo (in_array('Click', $two_events_after_load_check)) ? 'selected' : ''; ?> value="Click">Click</option>
                    </select>
                    <p class="description"><?php _e('Events which are fired after page load', 'tenweb-speed-optimizer'); ?></p>
                </div>
                <div class="two_settings_option two_plugins_to_exclude" <?php if ($two_delay_all_js_execution != 'on') {
                    echo 'style="display:none"';
                } ?>>
                    <label for="two_delay_js_exclusions" class="wd-label"><?php _e(esc_html($TwoSettings->settings_names['two_delay_js_exclusions']['title']), 'tenweb-speed-optimizer'); ?></label>
                    <p class="description"><?php _e(esc_html($TwoSettings->settings_names['two_delay_js_exclusions']['description']), 'tenweb-speed-optimizer'); ?></p>
                    <select multiple="multiple" id="two_delay_js_exclusions" name="two_delay_js_exclusions">
                        <?php foreach ($exclusion_data as $key => $val) { ?>
                            <?php
                            $selected = '';

                            if (isset($val['type']) && ($val['type'] === 'script' || $val['type'] === 'plugin' || $val['type'] === 'theme')) {
                                $val['Name'] = $val['title'];

                                if ($val['type'] === 'theme') {
                                    $val['Name'] .= ' (theme)';
                                }
                            } else {
                                $val['Name'] .= ' (folder)';
                            }

                            if (isset($two_delay_js_exclusions) && is_array($two_delay_js_exclusions)) {
                                if (in_array($key, $two_delay_js_exclusions)) {
                                    $selected = 'selected';
                                }
                            }
                            ?>
                            <option <?php echo esc_html($selected); ?> value="<?php echo esc_attr($key); ?>"><?php echo esc_html($val['Name']); ?></option>
                        <?php }?>
                    </select>
                </div>
                <div class="two_settings_option two_disabled_delay_all_js_pages" <?php if ($two_delay_all_js_execution != 'on') {
                                echo 'style="display:none"';
                            } ?>>
                    <label for="two_disabled_delay_all_js_pages"
                           class="wd-label"><?php _e('Excluded delayed JS Pages', 'tenweb-speed-optimizer'); ?></label>
                    <textarea style="width: 100%" name="two_disabled_delay_all_js_pages"
                              id="two_disabled_delay_all_js_pages"><?php echo isset($two_disabled_delay_all_js_pages) ? esc_textarea($two_disabled_delay_all_js_pages) : ''; ?></textarea>
                    <p class="description">
                      <?php _e('Use this textbox to specify Pages and exclude them from the optimization.', 'tenweb-speed-optimizer'); ?><br />
                      "^/[^.]+$" use this expression to exclude all pages except the homepage (w/o quotes)<br />
                      "^/$" use this expression to exclude homepage (w/o quotes)<br />
                      "^((?!red|green|blue).)*$" use this expression to exclude every string that not contains red, green or blue words (w/o quotes)<br />
                      add any subdirectory before the slash in expression (e.g. /wordpress/) if the site is in subdirectory.
                    </p>
                </div>

                <div class="two_settings_option two_exclude_delay_js" <?php if ($two_delay_all_js_execution != 'on') {
                                echo 'style="display:none"';
                            } ?>>
                    <label for="two_exclude_delay_js"
                           class="wd-label"><?php _e(esc_html($TwoSettings->settings_names['two_exclude_delay_js']['title']), 'tenweb-speed-optimizer'); ?></label>
                    <textarea style="width: 100%" name="two_exclude_delay_js"
                              id="two_exclude_delay_js"><?php echo isset($two_exclude_delay_js) ? esc_textarea($two_exclude_delay_js) : ''; ?></textarea>
                    <p class="description"><?php _e(esc_html($TwoSettings->settings_names['two_exclude_delay_js']['description']), 'tenweb-speed-optimizer'); ?></p>
                </div>

                <div class="two_settings_option two_delay_custom_js_new" <?php if ($two_delay_all_js_execution != 'on') {
                                echo 'style="display:none"';
                            } ?>>
                    <label for="two_delay_custom_js_new"
                           class="wd-label"><?php _e('JS callback', 'tenweb-speed-optimizer'); ?></label>
                    <textarea style="width: 100%" name="two_delay_custom_js_new"
                              id="two_delay_custom_js_new"><?php echo isset($two_delay_custom_js_new) ? esc_textarea($two_delay_custom_js_new) : ''; ?></textarea>
                    <p class="description"><?php _e('JS callback that will get fired after event.', 'tenweb-speed-optimizer'); ?></p>
                </div>





                <div class="two_js_options" <?php if ($two_delay_all_js_execution == 'on') {
                                echo 'style="display:none"';
                            } ?>>
                    <div class="two_settings_option">
                        <input <?php echo esc_html($aggregate_js_check); ?> type="checkbox" name="two_aggregate_js"
                                                                  id="enable_js_aggregate">
                        <label for="enable_js_aggregate"
                               class="wd-label"><?php _e('Aggregate JS files', 'tenweb-speed-optimizer'); ?></label>
                        <p class="description"><?php _e('Enable this option to aggregate JS files', 'tenweb-speed-optimizer'); ?></p>
                    </div>
                    <div class="two_settings_option two_include_inline_js" <?php if ($two_aggregate_js !== 'on') {
                                echo 'style="display:none"';
                            } ?>>
                        <input <?php echo esc_html($two_include_inline_js_check); ?> type="checkbox" name="two_include_inline_js"
                                                                           id="two_include_inline_js">
                        <label for="two_include_inline_js"
                               class="wd-label"><?php _e('Include inline JS', 'tenweb-speed-optimizer'); ?></label>
                        <p class="description"><?php _e('Check this option to include the internal JS scripts of your web pages in the optimization.', 'tenweb-speed-optimizer'); ?></p>
                    </div>
                    <div class="two_settings_option two_use_extended_exception_list_js" <?php if ($two_include_inline_js !== 'on') {
                                echo 'style="display:none"';
                            } ?>>
                        <input <?php echo esc_html($two_use_extended_exception_list_js); ?> type="checkbox" name="two_use_extended_exception_list_js"
                                                                                  id="two_use_extended_exception_list_js">
                        <label for="two_use_extended_exception_list_js"
                               class="wd-label"><?php _e('Use extended exception list for inline script aggregation', 'tenweb-speed-optimizer'); ?></label>
                        <p class="description"><?php _e('Check this option to exclude more internal scripts from optimization.', 'tenweb-speed-optimizer'); ?></p>
                    </div>
                    <div class="two_settings_option">
                        <input <?php echo esc_html($two_dequeue_jquery_migrate); ?> type="checkbox" name="two_dequeue_jquery_migrate"
                                                                          id="two_dequeue_jquery_migrate">
                        <label for="two_dequeue_jquery_migrate"
                               class="wd-label"><?php _e('Remove jQuery Migrate', 'tenweb-speed-optimizer'); ?></label>
                        <p class="description"><?php _e('Enable this checkbox to stop loading jquery-migrate.js file.', 'tenweb-speed-optimizer'); ?></p>
                    </div>
                    <div class="two_settings_option">
                        <input <?php echo esc_html($two_minify_js); ?> type="checkbox" name="two_minify_js"
                                                             id="two_minify_js">
                        <label for="two_minify_js"
                               class="wd-label"><?php _e('Minify JS files', 'tenweb-speed-optimizer'); ?></label>
                        <p class="description"><?php _e('Enable this checkbox to minify JS files', 'tenweb-speed-optimizer'); ?></p>
                    </div>

                    <div class="two_settings_option two_delay_js_execution">
                        <input <?php echo esc_html($two_delay_js_execution_check); ?> type="checkbox" name="two_delay_js_execution"
                                                                            id="two_delay_js_execution">
                        <label for="two_delay_js_execution"
                               class="wd-label"><?php _e('Delay JS execution', 'tenweb-speed-optimizer'); ?></label>
                        <p class="description"><?php _e('Enable this option to delay javascript execution', 'tenweb-speed-optimizer'); ?></p>
                    </div>
                    <div class="two_settings_option two_delayed_js_load_libs_first" <?php if ($two_delay_js_execution !== 'on') {
                                echo 'style="display:none"';
                            } ?>>
                        <input <?php echo esc_html($two_delayed_js_load_libs_first); ?> type="checkbox" name="two_delayed_js_load_libs_first"
                                                                              id="two_delayed_js_load_libs_first">
                        <label for="two_delayed_js_load_libs_first"
                               class="wd-label"><?php _e('Load delayed JS libraries first', 'tenweb-speed-optimizer'); ?></label>
                        <p class="description"><?php _e('Prioritize loading delayed scripts over inline scripts.', 'tenweb-speed-optimizer'); ?></p>
                    </div>
                    <div class="two_settings_option two_delayed_js_execution_list" <?php if ($two_delay_js_execution !== 'on') {
                                echo 'style="display:none"';
                            } ?>>
                        <label for="two_delayed_js_execution_list"
                               class="wd-label"><?php _e('Delay Scripts from Execution', 'tenweb-speed-optimizer'); ?></label>
                        <textarea style="width: 100%" name="two_delayed_js_execution_list"
                                  id="two_delayed_js_execution_list"><?php echo isset($two_delayed_js_execution_list) ? esc_textarea($two_delayed_js_execution_list) : ''; ?></textarea>
                        <p class="description"><?php _e('Use this textbox to specify  JavaScript files to be delayed.', 'tenweb-speed-optimizer'); ?></p>
                    </div>

                    <div class="two_settings_option">
                        <label for="two_exclude_js"
                               class="wd-label"><?php _e('Excluded JS Files', 'tenweb-speed-optimizer'); ?></label>
                        <textarea style="width: 100%" name="two_exclude_js"
                                  id="two_exclude_js"><?php echo isset($two_exclude_js) ? esc_textarea($two_exclude_js) : ''; ?></textarea>
                        <p class="description"><?php _e('Use this textbox to specify JavaScript files and exclude them from the optimization.', 'tenweb-speed-optimizer'); ?></p>
                    </div>
                </div>

            </div>
            <div class="two_settings_tab two_tab_lazy">
                <div class="two_settings_option">
                    <select name="lazy_load_type" id="lazy_load_type">
                        <option <?php echo ($lazy_load_type === 'browser') ? 'selected' : ''; ?> value="browser">Browser</option>
                        <option <?php echo ($lazy_load_type === 'vanilla') ? 'selected' : ''; ?> value="vanilla">Vanilla</option>
                    </select>
                    <label for="lazy_load_type" class="wd-label"><?php _e('Lazy load type', 'tenweb-speed-optimizer'); ?></label>
                </div>
                <p class="description"><?php _e('Select lazy load type', 'tenweb-speed-optimizer'); ?></p>
                <div class="two_settings_option">
                    <input <?php echo esc_html($lazyload_check); ?> type="checkbox" name="two_lazyload" id="enable_lazyload">
                    <label for="enable_lazyload"
                           class="wd-label"><?php _e(esc_html($TwoSettings->settings_names['two_lazyload']['title']), 'tenweb-speed-optimizer'); ?></label>
                </div>
                <p class="description"><?php _e(esc_html($TwoSettings->settings_names['two_lazyload']['description']), 'tenweb-speed-optimizer'); ?></p>
                <div class="two_settings_option two_add_noscript" style='<?php echo esc_attr($add_noscript_style); ?>'>
                    <input <?php echo esc_html($add_noscript_check); ?> type="checkbox" name="two_add_noscript" id="enable_add_noscript">
                    <label for="enable_add_noscript"
                           class="wd-label"><?php _e('Add noscript tag to Lazy Load Images', 'tenweb-speed-optimizer'); ?></label>
                    <p class="description"><?php _e('Disable this option if you want to not add noscript tag to lazy load images.', 'tenweb-speed-optimizer'); ?></p>
                </div>
                <div class="two_settings_option">
                    <input <?php echo esc_html($two_lazyload_slider_images_check); ?> type="checkbox" name="two_lazyload_slider_images" id="two_lazyload_slider_images">
                    <label for="two_lazyload_slider_images"
                           class="wd-label"><?php _e('Lazy Load Slider Images', 'tenweb-speed-optimizer'); ?></label>
                </div>
                <p class="description"><?php _e('Enable this option to activate lazy loading for images in sliders. May cause UI breaks.', 'tenweb-speed-optimizer'); ?></p>
                <div class="two_settings_option two_bg_lazyload_cont" style="<?php echo esc_attr($show_bg_lazy_load); ?>">
                    <input <?php echo esc_html($lazyload_bg_check); ?> type="checkbox" name="two_bg_lazyload" id="enable_bg_lazyload">
                    <label for="enable_bg_lazyload"
                           class="wd-label"><?php _e(esc_html($TwoSettings->settings_names['two_bg_lazyload']['title']), 'tenweb-speed-optimizer'); ?></label>
                    <p class="description"><?php _e(esc_html($TwoSettings->settings_names['two_bg_lazyload']['description']), 'tenweb-speed-optimizer'); ?></p>
                </div>
                <div class="two_settings_option two_img_in_viewport_lazyload_cont" style="<?php echo esc_attr($show_bg_lazy_load); ?>">
                    <input <?php echo esc_html($two_img_in_viewport_lazyload_check); ?> type="checkbox" name="two_img_in_viewport_lazyload" id="two_img_in_viewport_lazyload">
                    <label for="two_img_in_viewport_lazyload"
                           class="wd-label"><?php _e('Lazy Load for images that are not in the viewport', 'tenweb-speed-optimizer'); ?></label>
                    <p class="description"><?php _e('This option activates lazy load for images and excludes images in the viewport. To get images list in the viewport, generate critical CSS.', 'tenweb-speed-optimizer'); ?></p>
                </div>
                <div class="two_settings_option enable_youtube_vimeo_iframe_lazyload <?php echo ($lazy_load_type === 'browser') ? 'two_hidden' : ''; ?>">
                    <input <?php echo esc_html($lazyload_youtube_vimeo_iframe_check); ?> type="checkbox"
                                                                               name="two_youtube_vimeo_iframe_lazyload"
                                                                               id="enable_youtube_vimeo_iframe_lazyload">
                    <label for="enable_youtube_vimeo_iframe_lazyload"
                           class="wd-label"><?php _e('Replace Youtube, Vimeo Videos with Thumbnails', 'tenweb-speed-optimizer'); ?></label>
                    <p class="description"><?php _e('Enable this option to lazy load youtube, vimeo videos until you click the thumbnail.', 'tenweb-speed-optimizer'); ?></p>
                </div>
                <div class="two_settings_option">
                    <input <?php echo esc_html($lazyload_iframe_check); ?> type="checkbox" name="two_iframe_lazyload"
                                                                 id="enable_iframe_lazyload">
                    <label for="enable_iframe_lazyload"
                           class="wd-label"><?php _e('Lazy Load for Iframes', 'tenweb-speed-optimizer'); ?></label>
                    <p class="description"><?php _e('Enable this option to activate lazy loading for iframes on your website pages.', 'tenweb-speed-optimizer'); ?></p>
                </div>
                <div class="two_settings_option two_delay_iframe_lazyload" <?php if ($two_iframe_lazyload != 'on') {
                                echo 'style="display:none"';
                            } ?>>
                    <input <?php echo esc_html($delay_lazyload_iframe_check); ?> type="checkbox" name="two_delay_iframe_lazyload"
                                                                             id="enable_delay_iframe_lazyload">
                    <label for="enable_delay_iframe_lazyload"
                           class="wd-label"><?php _e('Delay Lazy Load for Iframes', 'tenweb-speed-optimizer'); ?></label>
                    <p class="description"><?php _e('Enable this option to activate delayed lazy loading for iframes on your website pages.', 'tenweb-speed-optimizer'); ?></p>
                </div>


                <div style="<?php echo ($two_iframe_lazyload == 'on') ? 'display:block;' : 'display:none;'; ?>" class="two_settings_option two_elemrntor_video_iframe">
                    <input <?php echo esc_html($two_elemrntor_video_iframe_check); ?> type="checkbox" name="two_elemrntor_video_iframe"
                                                                 id="two_elemrntor_video_iframe">
                    <label for="two_elemrntor_video_iframe"
                           class="wd-label"><?php _e('Elementor youtube block to iframe', 'tenweb-speed-optimizer'); ?></label>
                    <p class="description"><?php _e('Enable this option for convert elementor youtube block to iframe.', 'tenweb-speed-optimizer'); ?></p>
                </div>


                <div class="two_settings_option two_remove_elementor_lazyload">
                    <input <?php echo esc_html($two_remove_elementor_lazyload_check); ?> type="checkbox" name="two_remove_elementor_lazyload"
                                                                                        id="two_remove_elementor_lazyload">
                    <label for="two_remove_elementor_lazyload"
                           class="wd-label"><?php _e('Remove Elementor default lazyload', 'tenweb-speed-optimizer'); ?></label>
                    <p class="description"><?php _e('Enable this option if you want to remove elementor default lazyload.', 'tenweb-speed-optimizer'); ?></p>
                </div>


                <div class="two_settings_option">
                    <input <?php echo esc_html($lazyload_video_check); ?> type="checkbox" name="two_video_lazyload"
                                                                id="enable_video_lazyload">
                    <label for="enable_video_lazyload"
                           class="wd-label"><?php _e('Lazy Load for Videos', 'tenweb-speed-optimizer'); ?></label>
                    <p class="description"><?php _e('Enable this option to activate lazy loading for videos on your website pages.', 'tenweb-speed-optimizer'); ?></p>
                </div>
                <div class="two_settings_option two_exclude_lazyload" style='<?php echo esc_attr($exclude_lazyload); ?>'>
                    <label for="two_exclude_lazyload"
                           class="wd-label"><?php _e('Exclude from Lazy Load Images/Videos/Iframes', 'tenweb-speed-optimizer'); ?></label>
                    <textarea style="width: 100%" rows="6" name="two_exclude_lazyload"
                              id="two_exclude_lazyload"><?php echo isset($two_exclude_lazyload) ? esc_textarea($two_exclude_lazyload) : ''; ?></textarea>
                    <p class="description"><?php _e('Exclude Lazy Load Images Enter the image names or paths that should be excluded from lazy loading.', 'tenweb-speed-optimizer'); ?></p>
                </div>
            </div>
            <div class="two_settings_tab two_tab_image">
              <div class="two_settings_option">
                <input <?php echo esc_html($two_serve_optimized_bg_image); ?> type="checkbox" name="two_serve_optimized_bg_image"
                                                                    id="two_serve_optimized_bg_image">
                <label for="two_serve_optimized_bg_image"
                       class="wd-label"><?php _e('Implement Optimized Background Images', 'tenweb-speed-optimizer'); ?></label>
              </div>
              <p class="description"><?php _e('Enable this option to use small background images for mobile and tablet devices', 'tenweb-speed-optimizer'); ?></p>
              <?php
              if (function_exists('wp_filter_content_tags') || function_exists('wp_make_content_images_responsive')) {
                  ?>
              <div class="two_settings_option">
                <input <?php echo esc_html($two_enable_use_srcset); ?> type="checkbox" name="two_enable_use_srcset"
                                                             id="two_enable_use_srcset">
                <label for="two_enable_use_srcset"
                       class="wd-label"><?php _e('Enable responsive images', 'tenweb-speed-optimizer'); ?></label>
              </div>
              <p class="description"><?php _e('Enable this option to use small images for mobile and tablet devices', 'tenweb-speed-optimizer'); ?></p>
              <?php
              } ?>
              <div class="two_settings_option">
                <input <?php echo esc_html($two_do_not_optimize_images); ?> type="checkbox" name="two_do_not_optimize_images"
                                                                  id="do_not_optimize_images">
                <label for="do_not_optimize_images"
                       class="wd-label"><?php _e('Do Not Optimize Images', 'tenweb-speed-optimizer'); ?></label>
              </div>
              <p class="description"><?php _e('Enable this option to disable pagspeed module image optimization on your website homepage.', 'tenweb-speed-optimizer'); ?></p>
              <div class="two_settings_option two_exclude_images_for_optimize"
                   style='<?php echo esc_attr($exclude_images_for_optimize); ?>'>
                <label for="two_exclude_images_for_optimize"
                       class="wd-label"><?php _e('Exclude Images from Optimization', 'tenweb-speed-optimizer'); ?></label>
                <textarea style="width: 100%" rows="6" name="two_exclude_images_for_optimize"
                          id="two_exclude_images_for_optimize"><?php echo isset($two_exclude_images_for_optimize) ? esc_textarea($two_exclude_images_for_optimize) : ''; ?></textarea>
                <p class="description"><?php _e('Exclude Images for optimization Enter the image names or paths that should be excluded from pagespeed module optimization process.', 'tenweb-speed-optimizer'); ?></p>
              </div>

              <?php
              if (TENWEB_SO_HOSTED_ON_10WEB) {
                  ?>
              <div class="two_settings_option">
                  <input <?php echo esc_html($two_enable_nginx_webp_delivery); ?> type="checkbox" name="two_enable_nginx_webp_delivery"
                                                                        id="two_enable_nginx_webp_delivery">
                  <label for="two_enable_nginx_webp_delivery"
                         class="wd-label"><?php _e('Deliver WebP images if available', 'tenweb-speed-optimizer'); ?></label>
              </div>
              <?php
              } else {
                  // NginX server
                  if (TENWEB_SO_HOSTED_ON_NGINX) {
                      ?>
                  <div class="notice notice-info">
                    <p><?php _e('Your site is hosted on Nginx server.', 'tenweb-speed-optimizer'); ?></p>
                    <p><?php _e('To enable WebP delivery you\'ll need to modify hosting configs', 'tenweb-speed-optimizer'); ?></p>
                    <p><?php _e('Add this block before the server directive:', 'tenweb-speed-optimizer'); ?></p>
                    <pre><code>map $http_accept $webp_suffix {
    default "";
    "~*webp" ".webp";
}</code></pre>
                    <p><?php _e('Add this block inside the server directive:', 'tenweb-speed-optimizer'); ?></p>
                    <pre><code>location ~* ^.+\.(png|jpe?g)$ {
    add_header Vary Accept;
    try_files $uri$webp_suffix $uri =404;
}</code></pre>
                  </div>
                  <?php
                  } else {
                      ?>
                  <div class="two_settings_option">
                    <input <?php echo esc_html($two_enable_htaccess_webp_delivery);
                      disabled(!TENWEB_SO_HTACCESS_WRITABLE); ?> type="checkbox" name="two_enable_htaccess_webp_delivery"
                                                                          id="two_enable_htaccess_webp_delivery">
                    <label for="two_enable_htaccess_webp_delivery"
                           class="wd-label"><?php _e('Deliver WebP images if available using rewrite rules.', 'tenweb-speed-optimizer'); ?></label>
                    <?php
                    if (!TENWEB_SO_HTACCESS_WRITABLE) {
                        ?>
                      <p class="description"><?php _e('Your .htaccess file cannot be written. Please fix this and then return to this page to enable this option.', 'tenweb-speed-optimizer'); ?></p>
                      <?php
                    } ?>
                  </div>
                  <?php
                  }
                  $two_webp_delivery_working = OptimizerUtils::testWebPDelivery(); ?>
                <div id="two_webp_delivery_working" class="notice notice-success<?php echo $two_webp_delivery_working ? '' : ' two_hidden'; ?>">
                  <p><?php _e('WebP images delivery is enabled on hosting.', 'tenweb-speed-optimizer'); ?></p>
                </div>
                <?php
                ?>
                <div class="two_settings_option two_enable_picture_webp_delivery<?php echo $two_webp_delivery_working ? ' two_hidden' : ''; ?>">
                  <input <?php echo esc_html($two_enable_picture_webp_delivery); ?> type="checkbox" name="two_enable_picture_webp_delivery"
                                                                                                            id="two_enable_picture_webp_delivery">
                  <label for="two_enable_picture_webp_delivery"
                         class="wd-label"><?php _e('Deliver WebP images if available using &lt;picture&gt; tags.', 'tenweb-speed-optimizer'); ?></label>
                  <p class="description"><?php _e('Each &lt;img&gt; will be replaced with a &lt;picture&gt; tag. Some themes may break, so make sure to verify that everything seems fine.', 'tenweb-speed-optimizer'); ?></p>
                </div>
                <?php
              }
                ?>
                <div class="two_settings_option two_create_webp_list">
                  <label for="two_create_webp_list"
                         class="wd-label"><?php _e('Image or page URLs to generate WebP', 'tenweb-speed-optimizer'); ?></label>
                  <textarea style="width: 100%" rows="6" id="two_create_webp_list"></textarea>
                  <p class="description"><?php _e('Enter image url or page url (one per line) to create WebP versions. Leave this empty to generate for all images. Enter only absolute urls from this site. Other urls will be ignored.', 'tenweb-speed-optimizer'); ?></p>
                </div>
                <div class="two_settings_option">
                  <div class="two_webp_success_message notice notice-success is-dismissible">
                    <p><?php _e('Request is successfully sent. It might take some time to complete it.', 'tenweb-speed-optimizer'); ?></p>
                  </div>
                  <div class="two_webp_error_message notice notice-error is-dismissible">
                    <p><?php _e('Something went wrong.', 'tenweb-speed-optimizer'); ?></p>
                  </div>
                  <div>
                    <button class="two_webp_action two_regenerate_webp button" data-action="regenerate_webp"><?php _e(TENWEB_SO_HOSTED_ON_10WEB ? 'Regenerate WebP Images' : 'Regenerate WebP Images for Homepage', 'tenweb-speed-optimizer'); ?></button>
                    <button style="margin-left: 5px;" class="two_webp_action two_delete_webp button" data-action="delete_webp"><?php _e('Delete WebP Images', 'tenweb-speed-optimizer'); ?></button>
                  </div>
                </div>
            </div>
            <div class="two_settings_tab two_tab_non_optimizable_pages">
                <div class="two_settings_option">
                    <label for="two_non_optimizable_speed_optimizer_pages"
                           class="wd-label"><?php _e('Non Optimizable Pages List', 'tenweb-speed-optimizer'); ?></label>
                    <textarea style="width: 100%" rows="6" name="two_non_optimizable_speed_optimizer_pages"
                              id="two_non_optimizable_speed_optimizer_pages"><?php echo isset($two_non_optimizable_speed_optimizer_pages) ? esc_textarea($two_non_optimizable_speed_optimizer_pages) : ''; ?></textarea>
                    <p class="description">
                        <?php _e('Pages with such url\'s are non Optimizable, or we are not recommend to opimize them', 'tenweb-speed-optimizer'); ?>
                    </p>
                </div>
            </div>
            <div class="clear"></div>
        </form>
        <div class="two_settings_tab two_tab_page_css">
            <div class="two_table_container">
                <p class="two_page_css_table_desc description"><?php _e('Below is the list of CSS files which are disabled or load asynchronously on specific pages.', 'tenweb-speed-optimizer'); ?></p>
                <table id="two_page_css_table" class="display" style="width:100%">
                <thead>
                <tr>
                    <th>Css name</th>
                    <th>Url</th>
                    <th>Type</th>
                    <th>Edit/Duplicate/Delete</th>
                </tr>
                </thead>
                <tbody>
                <?php echo wp_kses_post($table_data); ?>
                </tbody>
                <tfoot>
                <tr>
                    <th>Css name</th>
                    <th>Url</th>
                    <th>Type</th>
                    <th>Edit/Duplicate/Delete</th>
                </tr>
                </tfoot>
            </table>
            </div>
        </div>
        <?php require_once TENWEB_SO_DIR . 'views/templates/two_critical_css.php'; ?>
        <div class="two_settings_tab two_tab_export_import">
            <div class="two_export_import">
                <p class="two_export_import_desc description"><?php _e('Export import settings', 'tenweb-speed-optimizer'); ?></p>
                <a href="/?two_export" download class="two_export button" >Export settings</a>
                <form action="" method="post" enctype="multipart/form-data">
                    Select json file to upload:
                    <input type="file" name="two_import" id="two_import">
                    <input type="submit" class="button two_import_settings_button" name="two_import_settings" value="Upload json settings" name="submit">
                </form>
            </div>
        </div>
        <?php require_once TENWEB_SO_DIR . 'views/templates/two_logs.php'; ?>
        <div class="two_settings_actions">
            <div class="two_message two_success_message notice notice-success is-dismissible">
                <p><?php _e('Success!', 'tenweb-speed-optimizer'); ?></p>
            </div>
            <div class="two_message two_error_message notice notice-error is-dismissible">
                <p><?php _e('Something went wrong.', 'tenweb-speed-optimizer'); ?></p>
            </div>
            <div class="buttons_actions">
                <div class="wd-left-side">
                    <button class="two_save_settings button button-primary">
                        <?php _e('Save settings', 'tenweb-speed-optimizer'); ?>
                    </button>
                    <span class="spinner"></span>
                </div>
                <div class="wd-left-side">
                    <button class="two_clear_cache button" data-from="settings_page"><?php _e('Clear Cache', 'tenweb-speed-optimizer'); ?></button>
                    <button style="margin-left: 5px;" class="two_regenerate_critical button"><?php _e('Regenerate All Critical CSS', 'tenweb-speed-optimizer'); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
