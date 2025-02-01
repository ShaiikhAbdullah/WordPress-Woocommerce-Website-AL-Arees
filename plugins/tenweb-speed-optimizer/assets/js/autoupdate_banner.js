jQuery(document).ready( function(){
    jQuery('.two-set-autoupdate').on('click', function(){
        two_set_autoupdate_data(jQuery(this));
    });
});

function two_set_autoupdate_data(that) {
    two_close_banner();
    let auto_update = '';
    if ( jQuery(that).hasClass('two-enable-autoupdate') ) {
        auto_update = 'enable';
    }
    jQuery.ajax({
        type: 'POST',
        url: two_speed.ajax_url,
        dataType: 'json',
        data: {
            action: 'two_set_autoupdate_from_banner',
            auto_update: auto_update,
            nonce: two_speed.nonce,
        }
    }).success(function ($result) {
        console.log($result);
    });
}