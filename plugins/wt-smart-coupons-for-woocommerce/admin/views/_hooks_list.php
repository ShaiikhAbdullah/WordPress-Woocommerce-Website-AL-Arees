<?php
/**
 *  Show usefull hooks and examples
 *  
 *  @since 1.5.2
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

include WT_SMARTCOUPON_MAIN_PATH . '/admin/data/data.hooks-help.php'; //include hooks list array

?>
<script type="text/javascript">
jQuery(document).ready(function() {
    
    hljs.highlightAll(); /* enable code view */


    /**
     *  Copy to clipboard
     */
    jQuery('.wt_sc_hooks_help_hook_con pre').prepend('<span class="copy_btn" title="<?php esc_attr_e('Copy to clipboard', 'wt-smart-coupons-for-woocommerce'); ?>"><?php esc_html_e('Copy', 'wt-smart-coupons-for-woocommerce'); ?></span>'); /* add copy button */

    jQuery(document).on('click', '.wt_sc_hooks_help_hook_con .copy_btn', function(){
        
        var elm = jQuery(this);
        var target_elm = jQuery(this).siblings('code');

        if(target_elm.length && "" !== target_elm.text().trim()) {
            
            navigator.clipboard.writeText(target_elm.text().trim());
            elm.text(WTSmartCouponAdminOBJ.msgs.copied);

            setTimeout(function(){ 
                elm.text('<?php esc_html_e('Copy', 'wt-smart-coupons-for-woocommerce'); ?>'); 
            }, 500);
        }
    });


    /**
     *  Accordian for the code examples.
     */
    jQuery(document).on('click', '.wt_sc_hooks_help_hook_hd', function() {
        
        var elm = jQuery(this);
        var target_elm = jQuery(this).siblings('.wt_sc_hooks_help_hook_inner');

        if(target_elm.is(':visible')) {
            target_elm.slideUp();
            elm.find('.dashicons').removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
        } else {
            target_elm.slideDown();
            elm.find('.dashicons').removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-up-alt2');
        }
        
    });

});

</script>
<style type="text/css">
.wt_sc_hooks_help_container{ width:100%; height:auto; border:solid 1px #c3c4c7; border-bottom:none; margin-bottom:20px; }
.wt_sc_hooks_help_container *{ box-sizing:border-box; }
.wt_sc_hooks_help_hook_item{width:100%; padding:10px 5px;}
.wt_sc_hooks_help_cat_hd{ background:#ececec; border-bottom:solid 1px #c3c4c7; font-weight:600; font-size:15px; }
.wt_sc_hooks_help_hook_con{ border-bottom:solid 1px #c3c4c7; border-top:solid 1px #eee; font-weight:400;}
.wt_sc_hooks_help_hook_hd{ background:#fff; font-size:14px; font-weight:500; background:#fbfbfb; cursor:pointer;}
.wt_sc_hooks_help_hook_hd .dashicons{ float:right;}
.wt_sc_hooks_help_hook_inner{ padding:5px 10px; padding-bottom:20px; display:none;}
.wt_sc_hooks_help_hook_desc{ padding:5px 10px; }
.wt_sc_hooks_help_hook_con pre{ padding:0px 10px; margin:0px; position:relative; }
.wt_sc_hooks_help_hook_con code{ width:100%; display:block; padding:20px; margin:0px; box-shadow:0px 0px 3px 0px #bbb; }
.wt_sc_hooks_help_hook_con .copy_btn{position:absolute; right:20px; top:10px; padding:3px 10px; background:rgba(0, 0, 0, .5); color:#fff; font-size:12px; cursor:pointer; }
.wt_sc_hooks_help_hook_con .copy_btn:hover{ background:rgba(0, 0, 0, 1); }
.wt_sc_hooks_help_hook_con .copy_btn:active{ background:rgba(9, 117, 9, 1); }
</style>

<h3><?php _e('Hooks', 'wt-smart-coupons-for-woocommerce'); ?> </h3>
<p><?php _e("Some useful `hooks` to extend the plugin's functionality", 'wt-smart-coupons-for-woocommerce');?> </p>

<div class="wt_sc_hooks_help_container">
    <?php
    foreach( $wf_filters_help_doc_lists as $filter_cat => $filter_data_arr ) {
        
        ?>
        <div class="wt_sc_hooks_help_hook_item wt_sc_hooks_help_cat_hd"> <?php echo esc_html(isset($hooks_category_labels[ $filter_cat ]) ? $hooks_category_labels[ $filter_cat ] : ''); ?></div>
        <?php
        foreach( $filter_data_arr as $filter => $filter_data ) {
            
            $filter_title = ( isset( $filter_data['title'] ) ? $filter_data['title'] : '' );
            
            if( !$filter_title ) {
                continue;
            }
            ?>
            <div class="wt_sc_hooks_help_hook_con">
                <div class="wt_sc_hooks_help_hook_item wt_sc_hooks_help_hook_hd"><?php echo esc_html($filter_title); ?> <span class="dashicons dashicons-arrow-down-alt2"></span></div>
                <div class="wt_sc_hooks_help_hook_inner">
                    <div class="wt_sc_hooks_help_hook_desc"><?php echo wp_kses_post((isset($filter_data['description']) ? $filter_data['description'] : '')); ?></div>         
                    <pre><code><?php echo esc_html($filter_data['example']); ?></code></pre>
                </div>
            </div>
            <?php
        }
    }
    ?>
</div>
