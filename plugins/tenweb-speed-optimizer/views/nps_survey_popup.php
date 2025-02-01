<?php
$nps_data = get_option('two_nps_data');

global $pagenow;

if ($pagenow == 'index.php') {
    $two_nps_from = 'wp_dashboard';
} elseif ($pagenow == 'edit.php') {
    // phpcs:ignore
    if (isset($_GET['post_type']) && $_GET['post_type'] == 'page') {
        $two_nps_from = 'wp_pages_list';
    } else {
        $two_nps_from = 'wp_posts_list';
    }
} else {
    $two_nps_from = 'wp_two_main_page';
}

$nps_question = [
    'main_class' => 'two_nps_question',
    'main_image' => TENWEB_SO_URL . '/assets/images/nps_survey/nps_flash.png',
    'title' => __('Do you enjoy using 10Web Booster?', 'tenweb-speed-optimizer'),
    'description' => __(
        'How likely are you to recommend 10Web Booster<br> to someone struggling with website speed?',
        'tenweb-speed-optimizer'
    ),
    'show_rating' => true,
    'button' => [
        'text' => __('Submit', 'tenweb-speed-optimizer'),
        'class' => 'two-submit-nps-question two-button-disabled',
        'url' => '#',
    ],
];

$share_main_class = (isset($nps_data['show_share_love']) && $nps_data['show_share_love'] == 1) ? '' : 'two-hidden';
$nps_share_love = [
    'main_class' => 'two_nps_share_love ' . $share_main_class,
    'main_image' => TENWEB_SO_URL . '/assets/images/nps_survey/nps_loving.png',
    'title_image' => TENWEB_SO_URL . '/assets/images/nps_survey/stars.png',
    'title' => __('Thanks for loving 10Web Booster', 'tenweb-speed-optimizer'),
    'description' => __('We have tried our best to make it free, accessible and valuable for the WordPress community.<br>
        For us to keep doing that, please leave your review on ', 'tenweb-speed-optimizer') .
        '<a href="' . esc_url('https://wordpress.org/support/plugin/tenweb-speed-optimizer/reviews/?filter=5#new-post') . '" target="_blank">WP.org</a>'
        . __(
            ' which won’t take you longer than a minute.',
            'tenweb-speed-optimizer'
        ),
    'button' => [
        'text' => __('Share your love', 'tenweb-speed-optimizer'),
        'class' => 'two-nps-green-button',
        'url' => 'https://wordpress.org/support/plugin/tenweb-speed-optimizer/reviews/?filter=5#new-post',
    ],
];

$nps_sounds_good = [
    'main_class' => 'two_nps_sounds_good two-hidden',
    'main_image' => TENWEB_SO_URL . '/assets/images/nps_survey/nps_loving.png',
    'title' => __('Your feedback matters', 'tenweb-speed-optimizer'),
    'description' => __(
        'Thank you for your feedback and being a part of 10Web Booster.<br>
        You help us understand how we can improve our product and ensure the best experience for you.',
        'tenweb-speed-optimizer'
    ),
    'button' => [
        'text' => __('Sounds good', 'tenweb-speed-optimizer'),
        'class' => 'two-nps-green-button',
        'url' => '#',
    ],
];

if ($nps_data['show_nps_survey'] == 1) {
    //todo maybe add wp_kses() to output
    echo wp_kses_post(print_nps_content($nps_question, $two_nps_from));  // phpcs:ignore
    echo wp_kses_post(print_nps_content($nps_share_love, $two_nps_from));  // phpcs:ignore
    echo wp_kses_post(print_nps_content($nps_sounds_good, $two_nps_from));  // phpcs:ignore
} elseif (isset($nps_data['show_share_love']) && $nps_data['show_share_love'] == 1) {
    echo wp_kses_post(print_nps_content($nps_share_love, $two_nps_from));  // phpcs:ignore
}

function print_nps_content($nps_content, $nps_from)
{
    ?>
    <div class="two-banner-main-container <?php esc_attr_e($nps_content['main_class']); ?>"
        data-two-nps-from="<?php esc_attr_e($nps_from, 'tenweb-speed-optimizer'); ?>">
        <?php if (isset($nps_content['main_image'])) { ?>
        <img src="<?php echo esc_url($nps_content['main_image']); ?>" alt="NPS Survey Image" class="two-banner-main-image">
        <?php } ?>
        <span class="two-banner-close-button two-nps-close"></span>
        <div class="two-nps-container">
            <div class="two-nps-content">
                <div class="two-banner-title">
                    <?php
                    echo wp_kses($nps_content['title'], ['br' => []]);

    if (isset($nps_content['title_image'])) { ?>
                        <img src="<?php echo esc_url($nps_content['title_image']); ?>"
                             alt="NPS Stars" class="two-nps-title-image">
                    <?php } ?>
                </div>
                <div class="two-nps-description">
                    <?php echo wp_kses_post($nps_content['description']); ?>
                </div>
            </div>
            <?php echo (isset($nps_content['show_rating'])
                && $nps_content['show_rating']) ? nps_rating_part() : ''; // phpcs:ignore ?>
            <div class="two-banner-buttons-container">
                <a href="<?php echo esc_url($nps_content['button']['url']); ?>"
                   class="<?php echo esc_attr($nps_content['button']['class']); ?>" target="_blank">
                    <?php echo $nps_content['button']['text']; // phpcs:ignore ?>
                </a>
            </div>
        </div>
    </div>
<?php
}

function nps_rating_part()
{
    $rates = [
        [ 'rate' => '1', 'hover' => '#FFFFFF26', 'green' => '#22B3391A', 'orange' => '#F8C33219', 'red' => '#FC3B3119' ],
        [ 'rate' => '2', 'hover' => '#FFFFFF33', 'green' => '#22B33933', 'orange' => '#F8C23033', 'red' => '#FD3A304C' ],
        [ 'rate' => '3', 'hover' => '#FFFFFF3F', 'green' => '#22B43B4C', 'orange' => '#F8C2304C', 'red' => '#FD3A3066' ],
        [ 'rate' => '4', 'hover' => '#FFFFFF4C', 'green' => '#22B43B7F', 'orange' => '#F8C23072', 'red' => '#FD3A3099' ],
        [ 'rate' => '5', 'hover' => '#FFFFFF59', 'green' => '#22B43B7F', 'orange' => '#F8C23099', 'red' => '#FD3A30CC' ],
        [ 'rate' => '6', 'hover' => '#FFFFFF66', 'green' => '#22B43B99', 'orange' => '#F8C230B2', 'red' => '#FD3A30FF' ],
        [ 'rate' => '7', 'hover' => '#FFFFFF72', 'green' => '#22B43BB2', 'orange' => '#F8C230CC' ],
        [ 'rate' => '8', 'hover' => '#FFFFFF7F', 'green' => '#22B43BCC', 'orange' => '#F8C230FF' ],
        [ 'rate' => '9', 'hover' => '#FFFFFF8C', 'green' => '#22B43BE5', 'orange' => '#F8C230FF' ],
        [ 'rate' => '10', 'hover' => '#FFFFFF99', 'green' => '#22B43BFF' ],
    ]; ?>
    <div class="two-nps-rating-container">
        <div class="two-nps-rating">
        <?php
        foreach ($rates as $rate) { ?>
            <span class="two-nps-each-rate"
                  data-nps-rate="<?php echo esc_attr($rate['rate']); ?>"
                  data-nps-hover="<?php echo esc_attr($rate['hover']); ?>"
                  data-nps-green="<?php echo esc_attr($rate['green']);

                  if (isset($rate['orange'])) { ?>"
                  data-nps-orange="<?php echo esc_attr($rate['orange']);
                  }

if (isset($rate['red'])) { ?>"
                  data-nps-red="<?php echo esc_attr($rate['red']);
                  } ?>">
                <?php echo esc_html($rate['rate']); ?>
            </span>
        <?php } ?>
        </div>
        <div class="two-nps-text">
            <p><?php esc_html_e('Not at all likely', 'tenweb-speed-optimizer'); ?></p>
            <p><?php esc_html_e('Extremely likely', 'tenweb-speed-optimizer'); ?></p>
        </div>
    </div>
    <?php
}
