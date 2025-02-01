jQuery(document).ready(function(){
    jQuery('.two_deactivate_plugin').click(function(){
        var plugin_slug = jQuery(this).attr('data-plugin-slug');
        jQuery.ajax({
            type: "POST",
            url: two_admin_vars.ajaxurl,
            data: {
                'action': 'two_deactivate_plugins',
                'nonce': two_admin_vars.ajaxnonce,
                'plugin_slug': plugin_slug,
            },
        }).done(function (data) {
            location.reload();
        })
    })
})