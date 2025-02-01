jQuery("document").ready(function () {
    jQuery('#two-save-company-name').click(function(){
        if( jQuery('#two-company-name').val() != '' ) {
            let company_name = jQuery('#two-company-name').val();
            let support_url = jQuery('#two-support-url').val();
            change_company_name( company_name, support_url );
        }
    });
    jQuery('#two-white-label-status').click(function(){
        // to not allow check on toggle
        if( jQuery(this).is(":checked") ) {
            jQuery('#two-white-label-status').prop("checked", false);
        } else {
            jQuery('#two-company-name').val('');
            change_company_name( '', '' );
        }
    })
    jQuery('#two-company-name').on('keyup',function(){
        if( jQuery(this).val() != '' ) {
            jQuery('#two-save-company-name').prop('disabled', false);
        } else {
            jQuery('#two-save-company-name').prop('disabled', true);
        }
    })
})

function change_company_name(company_name, support_url) {
    jQuery.ajax({
        type: "POST",
        url: two_admin_vars.ajaxurl,
        data: {
            'action': 'two_white_label',
            'nonce': two_admin_vars.ajaxnonce,
            'company_name': company_name,
            'support_url': support_url,
        },
    }).done(function () {
        if( company_name != '' ) {
            location.reload();
        } else {
            jQuery('#two-save-company-name').prop('disabled', true);
        }
    })
}
