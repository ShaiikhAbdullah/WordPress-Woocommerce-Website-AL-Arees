<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div id="wt_give_away_free_products" class="panel woocommerce_options_panel">
    <?php 
    /**
     *  Normal coupon type giveaway tab content
     */
    include_once plugin_dir_path(__FILE__).'_normal_coupon_giveaway_tab_content.php'; 
    
    /**
     *  Bogo coupon type giveaway tab content
     */
    include_once plugin_dir_path(__FILE__).'_bogo_coupon_giveaway_tab_content.php'; 
    ?>
</div>