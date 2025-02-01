<?php
use TenWebOptimizer\OptimizerUtils;

$two_incompatible_plugins = OptimizerUtils::get_conflicting_plugins();

if (!TENWEB_SO_HOSTED_ON_10WEB && strtolower(TWO_SO_ORGANIZATION_NAME) === '10web') {
    ?>
<div class="two-header">
    <img src="<?php echo esc_url(TENWEB_SO_URL); ?>/assets/images/10web_logo.svg" alt="10Web" class="two-header-img" />
</div>
<?php
}

if (!empty($two_incompatible_plugins)) {
    ?>
<div class="two_incompatible_notice">
    <div class="two_incompatible_notice_title">Some plugins are conflicting with <?php echo esc_html(TWO_SO_ORGANIZATION_NAME); ?> Booster.</div>
    <div class="two_incompatible_notice_desc">
        Deactivate these plugins so the Booster can perform website optimization as intended.
        Proceeding without deactivation can reduce the efficiency of <?php echo esc_html(TWO_SO_ORGANIZATION_NAME); ?> Booster and cause technical issues.
    </div>
    <div class="two_incompatible_plugin_list">
    <?php
    foreach ($two_incompatible_plugins as $slug => $name) {
        ?>
    <div class="two_incompatible_plugins">
       <span class="two_incompatible_plugin_name"><?php echo esc_html($name); ?></span>
        <span class="two_deactivate_plugin" data-plugin-slug="<?php echo esc_attr($slug); ?>">Deactivate</span>
    </div>

    <?php
    } ?>
    </div>
</div>
<?php
}
?>
