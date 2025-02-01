<?php

namespace TenWebOptimizer;

use Elementor\Core\Settings\Manager as SettingsManager;

/**
 * Class OptimizerElementor
 */
class OptimizerElementor
{
    public function __construct()
    {
        add_action('elementor/editor/after_enqueue_scripts', [ $this, 'two_scripts_styles' ]);
        add_action('elementor/init', [ $this, 'two_add_panel_tab' ]);
        add_action('elementor/documents/register_controls', [ $this, 'two_register_document_controls' ]);
    }

    /* Enque scripts/styles for Elementor editor */
    public function two_scripts_styles()
    {
        global $post;
        $page_url = get_permalink($post->ID);

        if (!current_user_can('administrator')) {
            return;
        }
        wp_register_style('two-open-sans', 'https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,600,700,800&display=swap');
        wp_enqueue_style('two_speed_css', TENWEB_SO_URL . '/assets/css/speed.css', [ 'two-open-sans' ], TENWEB_SO_VERSION);
        wp_enqueue_style('two_speed_dark_css', TENWEB_SO_URL . '/assets/css/speed_elementor_dark.css', [ 'two-open-sans', 'elementor-editor-dark-mode' ], TENWEB_SO_VERSION);
        wp_enqueue_script('two_circle_js', TENWEB_SO_URL . '/assets/js/circle-progress.js', [ 'jquery' ], TENWEB_SO_VERSION);
        wp_enqueue_script('two_speed_js', TENWEB_SO_URL . '/assets/js/speed.js', [
            'jquery',
            'two_circle_js'
        ], TENWEB_SO_VERSION);
        wp_localize_script('two_speed_js', 'two_speed', [
            'nonce' => wp_create_nonce('two_ajax_nonce'),
            'ajax_url' => admin_url('admin-ajax.php'),
            'clearing' => __('Clearing...', 'tenweb-speed-optimizer'),
            'cleared' => __('Cleared cache', 'tenweb-speed-optimizer'),
            'clear' => __('Clear cache', 'tenweb-speed-optimizer'),
            'title' => __('10Web Booster', 'tenweb-speed-optimizer'),
            'optimize_entire_website' => \TenWebOptimizer\OptimizerOnInit::two_reached_limit(),
            'post_type' => $post->post_type,
            'post_status' => get_post_status($post->ID),
            'post_optimizable' => \TenWebOptimizer\OptimizerUrl::urlIsOptimizable($page_url),
        ]);
    }

    /* Register new tab in page settings */
    public function two_add_panel_tab()
    {
        if (!current_user_can('administrator')) {
            return;
        }
        \Elementor\Controls_Manager::add_tab(
            'two_optimize',
            esc_html__('10Web Booster', 'tenweb-speed-optimizer')
        );
    }

    /**
     * Register additional document controls.
     *
     * @param \Elementor\Core\DocumentTypes\PageBase $document the PageBase document instance
     */
    public function two_register_document_controls($document)
    {
        if (!current_user_can('administrator')) {
            return;
        }

        if (! $document instanceof \Elementor\Core\DocumentTypes\PageBase || ! $document::get_property('has_elements')) {
            return;
        }

        $document->start_controls_section(
            'two_optimize_section',
            [
                'tab' => 'two_optimize',
            ]
        );

        global $post;
        $post_id = $post->ID;

        /* Check home page */
        if (get_option('page_on_front') == $post_id) {
            $post_id = 'front_page';
            $page_score = get_option('two-front-page-speed');
        } else {
            $page_score = get_post_meta($post_id, 'two_page_speed', true);
        }
        $ui_theme = 'auto';

        if (class_exists('Elementor\Core\Settings\Manager')) {
            $ui_theme = SettingsManager::get_settings_managers('editorPreferences')->get_model()->get_settings('ui_theme');
        }
        $classname = 'two_elementor_' . $ui_theme . ' ';
        $status = 'optimized';
        $critical_pages = \TenWebOptimizer\OptimizerUtils::getCriticalPages();

        if (\TenWebWpTransients\OptimizerTransients::get('two_optimize_inprogress_' . $post_id)) {
            $status = 'optimizing';
        } elseif (!array_key_exists($post_id, $critical_pages)) {
            $status = 'notOptimized';
        }

        $reach_limit = \TenWebOptimizer\OptimizerOnInit::two_reached_limit();

        if ($status != 'optimized') {
            if ($reach_limit != false) {
                $label = '<p class="two_elementor_control_title' . ($status == 'optimizing' ? ' two-hidden' : '') . '">' . esc_html__('You’ve reached the Free Plan limit', 'tenweb-speed-optimizer') . '</p>';
            } else {
                $label = '<p class="two_elementor_control_title' . ($status == 'optimizing' ? ' two-hidden' : '') . '">' . esc_html__('Optimize with 10Web Booster', 'tenweb-speed-optimizer') . '</p>';
            }
            $content = $this->two_elementor_not_optimized_content($status, $post_id);
            $classname = $classname . 'two_elementor_settings_content' . ($status == 'optimizing' ? ' two-optimizing' : '');
        } else {
            $page_title = get_the_title($post_id);
            $label = '';
            $content = $this->two_elementor_optimized_content($page_title, $page_score, $status, $post_id);
            $classname = $classname . 'two_elementor_settings_content two_optimized';
        }

        $document->add_control(
            'raw_html',
            [
                'label' => $label,
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => $content,
                'content_classes' => $classname,
            ]
        );

        $document->end_controls_section();
    }

    /**
     * Elementor editor booster info in case of page not optimized
     *
     * @param $status  bool
     * @param $post_id integer
     *
     * @return string html data
     */
    public function two_elementor_not_optimized_content($status, $post_id)
    {
        $reach_limit = \TenWebOptimizer\OptimizerOnInit::two_reached_limit();
        ob_start();

        if ($reach_limit != false) { ?>
          <div class="two_elementor_control_container<?php echo $status == 'optimizing' ? ' two-hidden' : ''; ?>">
              <p class="two_elementor_control_container_description"><?php echo esc_html__('Upgrade to 10Web Booster Pro to optimize all pages and enable Cloudflare Enterprise CDN.', 'textdomain'); ?></p>
              <a href="<?php echo esc_url($reach_limit . '?two_comes_from=ElementorAfterLimit'); ?>" target="_blank" data-post-id="<?php echo esc_attr($post_id); ?>"
                 data-initiator="elementor" class="two_optimize_button_elementor two_optimize_button"><?php _e('Upgrade', 'tenweb-speed-optimizer'); ?>
              </a>
          </div>
      <?php } else { ?>
          <div class="two_elementor_control_container<?php echo $status == 'optimizing' ? ' two-hidden' : ''; ?>">
      <p><?php echo esc_html__('Get a 90+ PageSpeed score', 'textdomain'); ?></p>
          <a onclick="<?php echo 'two_optimize_page(this)'; ?>" data-post-id="<?php echo esc_attr($post_id); ?>"
             data-initiator="elementor" class="two_optimize_button_elementor two_optimize_button"><?php _e('Optimize now', 'tenweb-speed-optimizer'); ?>
          </a>
    </div>
    <span class="two-page-speed two-optimizing two-loading-bg <?php echo $status == 'optimizing' ? '' : ' two-hidden'; ?>">
    <?php _e('Optimizing...', 'tenweb-speed-optimizer'); ?>
    <p class="two-description"><?php _e('Reload in 2 minutes to see<br> the new score', 'tenweb-speed-optimizer'); ?></p>
  </span>
    <?php }

        return ob_get_clean();
    }

    /**
     * Elementor editor booster info in case of page optimized
     *
     * @param $page_title string
     * @param $score_data array
     *
     * @return string html data
     */
    public function two_elementor_optimized_content($page_title, $score_data, $status, $post_id)
    {
        $date = 0;

        if (!empty($score_data) && isset($score_data['current_score'])) {
            $optimized_pages = \TenWebOptimizer\OptimizerUtils::getCriticalPages();

            if (isset($optimized_pages[$post_id]) && isset($optimized_pages[$post_id]['critical_date'])) {
                $date = $optimized_pages[$post_id]['critical_date'];
            } elseif (isset($score_data['current_score']['date'])) {
                $date = strtotime($score_data['current_score']['date']);
            }
        }
        $modified_date = get_the_modified_date('d.m.Y h:i:s a', $post_id);
        $modified_date = strtotime($modified_date);
        $reanalyze_button_status_previous = false;
        $reanalyze_button_status_current = false;

        if (!empty($score_data)) {
            if (isset($score_data['current_score']) && isset($score_data['current_score']['status'])
                && $score_data['current_score']['status'] == 'inprogress') {
                $reanalyze_button_status_current = true;
            }

            if (isset($score_data['previous_score']) && isset($score_data['previous_score']['status'])
                && $score_data['previous_score']['status'] == 'inprogress') {
                $reanalyze_button_status_previous = true;
            }
        }
        ob_start(); ?>
    <script>
      jQuery('.two-score-circle').each(function () {
        two_draw_score_circle(this);
      });
    </script>
    <p class="two-elementor-container-title"><?php echo '<span>' . esc_html__($page_title) . '</span>' . esc_html__(' page is successfully optimized', 'textdomain'); ?></p>
    <div class="two-score-section two-any-reanalyzing-score-section" data-id="<?php echo esc_attr($post_id); ?>">
      <div class="two-score-container-both">
        <div class="two-score-container-old">
          <div class="two-score-header"><?php _e('Before optimization', 'tenweb-speed-optimizer'); ?></div>
            <?php if (empty($score_data) || !isset($score_data['previous_score']) || $reanalyze_button_status_previous) {
            $no_old_scores = 'two-no-scores';
        } ?>
            <div class="two-old-scores <?php echo isset($no_old_scores) ? esc_html($no_old_scores) : ''; ?>"
                 data-no-score-for="<?php echo $reanalyze_button_status_previous ? esc_attr($post_id) : ''; ?>">
                <div class="two-score-mobile">
                    <div class="two-score-circle two_circle_with_bg"
                         data-id="mobile"
                         data-thickness="2"
                         data-size="40"
                         data-score="<?php echo (!isset($no_old_scores) && isset($score_data['previous_score'])
                             && isset($score_data['previous_score']['mobile_score'])) ? (int) $score_data['previous_score']['mobile_score'] : ''; ?>"
                         data-loading-time="<?php echo (!isset($no_old_scores) && isset($score_data['previous_score'])
                             && isset($score_data['previous_score']['mobile_tti'])) ? esc_html($score_data['previous_score']['mobile_tti']) : ''; ?>">
                        <span class="two-score-circle-animated"></span>
                    </div>
                    <div class="two-score-text">
                        <span class="two-score-text-name"><?php _e('Mobile score', 'tenweb-speed-optimizer'); ?></span>
                        <span class="two-load-text-time"><?php _e('Load time: ', 'tenweb-speed-optimizer'); ?><span class="two-load-time"></span><?php _e('s', 'tenweb-speed-optimizer'); ?></span>
                    </div>
                </div>
                <div class="two-score-desktop">
                    <div class="two-score-circle two_circle_with_bg"
                         data-id="desktop"
                         data-thickness="2"
                         data-size="40"
                         data-score="<?php echo (!isset($no_old_scores) && isset($score_data['previous_score'])
                             && isset($score_data['previous_score']['desktop_score'])) ? (int) $score_data['previous_score']['desktop_score'] : ''; ?>"
                         data-loading-time="<?php echo (!isset($no_old_scores) && isset($score_data['previous_score'])
                             && isset($score_data['previous_score']['desktop_tti'])) ? esc_html($score_data['previous_score']['desktop_tti']) : ''; ?>">
                        <span class="two-score-circle-animated"></span>
                    </div>
                    <div class="two-score-text">
                        <span class="two-score-text-name"><?php _e('Desktop score', 'tenweb-speed-optimizer'); ?></span>
                        <span class="two-load-text-time"><?php _e('Load time: ', 'tenweb-speed-optimizer'); ?><span class="two-load-time"></span><?php _e('s', 'tenweb-speed-optimizer'); ?></span>
                    </div>
                </div>
                    <a onclick="<?php echo 'two_reanalyze_score(this)'; ?>" data-from-elementor="1" data-post_id="<?php echo esc_attr($post_id); ?>" target="_blank"
                       data-initiator="elementor" class="two_reanalyze_link <?php echo $reanalyze_button_status_previous ? 'two-hidden' : ''; ?>">
                    </a>
                    <span class="two-page-speed two-optimizing <?php echo $reanalyze_button_status_previous ? '' : 'two-hidden'; ?>"></span>
            </div>
        </div>
        <div class="two-score-container-new">
          <div class="two-score-header"><?php _e('After optimization', 'tenweb-speed-optimizer'); ?></div>
            <?php if (empty($score_data) || !isset($score_data['current_score']) || $reanalyze_button_status_current) {
                                 $no_new_scores = 'two-no-scores';
                             } ?>
            <div class="two-new-scores <?php echo isset($no_new_scores) ? esc_html($no_new_scores) : ''; ?>"
                 data-no-score-for="<?php echo $reanalyze_button_status_current ? esc_attr($post_id) : ''; ?>">
                <div class="two-score-mobile">
                    <div class="two-score-circle two_circle_with_bg"
                         data-id="mobile"
                         data-thickness="2"
                         data-size="40"
                         data-score="<?php echo (!isset($no_new_scores) && isset($score_data['current_score'])
                             && isset($score_data['current_score']['mobile_score'])) ? (int) $score_data['current_score']['mobile_score'] : ''; ?>"
                         data-loading-time="<?php echo (!isset($no_new_scores) && isset($score_data['current_score'])
                             && isset($score_data['current_score']['mobile_tti'])) ? esc_html($score_data['current_score']['mobile_tti']) : ''; ?>">
                      <span class="two-score-circle-animated"></span>
                    </div>
                    <div class="two-score-text">
                      <span class="two-score-text-name"><?php _e('Mobile score', 'tenweb-speed-optimizer'); ?></span>
                      <span class="two-load-text-time"><?php _e('Load time: ', 'tenweb-speed-optimizer'); ?>
                          <span class="two-load-time"></span><?php _e('s', 'tenweb-speed-optimizer'); ?></span>
                    </div>
              </div>
                <div class="two-score-desktop">
                <div class="two-score-circle two_circle_with_bg"
                     data-id="desktop"
                     data-thickness="2"
                     data-size="40"
                     data-score="<?php echo (!isset($no_new_scores) && isset($score_data['current_score'])
                         && isset($score_data['current_score']['desktop_score'])) ? (int) $score_data['current_score']['desktop_score'] : ''; ?>"
                     data-loading-time="<?php echo (!isset($no_new_scores) && isset($score_data['current_score'])
                         && isset($score_data['current_score']['desktop_tti'])) ? esc_html($score_data['current_score']['desktop_tti']) : ''; ?>">
                  <span class="two-score-circle-animated"></span>
                </div>
                <div class="two-score-text">
                  <span class="two-score-text-name"><?php _e('Desktop score', 'tenweb-speed-optimizer'); ?></span>
                  <span class="two-load-text-time"><?php _e('Load time: ', 'tenweb-speed-optimizer'); ?><span class="two-load-time"></span><?php _e('s', 'tenweb-speed-optimizer'); ?></span>
                </div>
              </div>
                <a onclick="<?php echo 'two_reanalyze_score(this)'; ?>" data-from-elementor="1" data-post_id="<?php echo esc_attr($post_id); ?>" target="_blank"
                   data-initiator="elementor" class="two_reanalyze_link <?php echo $reanalyze_button_status_current ? 'two-hidden' : ''; ?>">
                </a>
                <span class="two-page-speed two-optimizing <?php echo $reanalyze_button_status_current ? '' : 'two-hidden'; ?>"></span>
            </div>
        </div>
      </div>
        <div class="two_reanalyze_container">
            <span class="two-page-speed two-optimizing <?php echo $reanalyze_button_status_current ? '' : 'two-hidden'; ?>"></span>
            <a onclick="two_reanalyze_score(this)" data-from-elementor="1" data-post_id="<?php echo esc_attr($post_id); ?>"
                    data-initiator="elementor" class="two_reanalyze_button">
                <?php $reanalyze_button_status_current ? _e('Reanalyzing...', 'tenweb-speed-optimizer') : _e('Reanalyze', 'tenweb-speed-optimizer'); ?>
            </a>
        </div>
        <div class="two_elementor_control_container<?php echo ($modified_date > $date && $date != 0) ? '' : ' two-hidden'; ?>">
            <a onclick="two_optimize_page(this)"
               data-post-id="<?php echo esc_attr($post_id); ?>"
               data-initiator="elementor"
               class="two_optimize_button_elementor two_optimize_button <?php echo (!$reanalyze_button_status_current && !$reanalyze_button_status_previous) ? '' : ' two-button-disabled'; ?>">
                <?php _e('Re-optimize', 'tenweb-speed-optimizer'); ?>
            </a>
        </div>
    </div>
      <span class="two-page-speed two-optimizing two-loading-bg <?php echo $status == 'optimizing' ? '' : ' two-hidden'; ?>">
    <?php _e('Re-optimizing...', 'tenweb-speed-optimizer'); ?>
    <p class="two-description"><?php _e('Reload in 2 minutes to see<br> the new score', 'tenweb-speed-optimizer'); ?></p>
  </span>
    <?php
    return ob_get_clean();
    }
}
