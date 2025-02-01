jQuery(document).ready(function() {

    jQuery('.two-submit-nps-question').on('click', function(e) {
        e.preventDefault();
        if( !jQuery(this).hasClass('two-button-disabled')) {
            let nps_rate;
            nps_rate = jQuery('.two-nps-selected').data('nps-rate');

            jQuery('.two_nps_question').addClass('two-hidden');
            if (nps_rate == 10) {
                jQuery('.two_nps_share_love').removeClass('two-hidden');
            } else {
                jQuery('.two_nps_sounds_good').removeClass('two-hidden');
            }
            set_nps_data( nps_rate );
        }
    });

    jQuery('.two-nps-green-button').on('click', function(e) {
        if ( jQuery(this).attr('href') == '#' ) {
            e.preventDefault();
        }
        two_close_banner();
    });

    jQuery('.two_nps_question .two-banner-close-button').on('click', function() {
        set_nps_data( '-1' );
    });
    jQuery('.two_nps_share_love .two-banner-close-button').on('click', function() {
        set_nps_data( '11' );
    });
    jQuery('.two_nps_share_love .two-nps-green-button').on('click', function() {
        set_nps_data( '11' );
    });

    jQuery('.two-nps-each-rate:not(.two-nps-rated)').mouseenter( function() {
        let rate_number = jQuery(this).data('nps-rate');
        jQuery('.two-nps-each-rate:not(.two-nps-rated)').each( function(){
            if ( jQuery(this).data('nps-rate') <= rate_number ) {
                let bg_color = jQuery(this).data('nps-hover');
                jQuery(this).css( 'background-color', bg_color );
            }
        });
    }).mouseleave( function() {
        jQuery('.two-nps-each-rate:not(.two-nps-rated)').removeAttr("style");
    });

    jQuery('.two-nps-each-rate').on('click',function(){
        jQuery('.two-submit-nps-question').removeClass('two-button-disabled');
        jQuery('.two-nps-each-rate').removeClass('two-nps-selected');
        jQuery(this).addClass('two-nps-selected');

        if ( !jQuery( '.two-nps-rated' ).length ) {
            jQuery('.two-nps-each-rate').addClass('two-nps-rated');
        }
        jQuery('.two-nps-each-rate').removeAttr("style");
        let rate_number = jQuery(this).data('nps-rate');
        if ( rate_number > 9 ) {
            jQuery('.two-nps-each-rate').each(function () {
                if (jQuery(this).data('nps-rate') <= rate_number) {
                    let bg_color = jQuery(this).data('nps-green');
                    jQuery(this).css('background-color', bg_color);
                }
            });
        } else if ( rate_number < 10  && rate_number > 6 ) {
            jQuery('.two-nps-each-rate').each(function () {
                if (jQuery(this).data('nps-rate') <= rate_number) {
                    let bg_color = jQuery(this).data('nps-orange');
                    jQuery(this).css('background-color', bg_color);
                }
            });
        } else {
            jQuery('.two-nps-each-rate').each(function () {
                if (jQuery(this).data('nps-rate') <= rate_number) {
                    let bg_color = jQuery(this).data('nps-red');
                    jQuery(this).css('background-color', bg_color);
                }
            });
        }
    });

});

function set_nps_data( nps_rate ) {
    let nps_from;
    nps_from = jQuery('.two-banner-main-container').data('two-nps-from');
    jQuery.ajax({
        type: 'POST',
        url: two_speed.ajax_url,
        dataType: 'json',
        data: {
            action: 'two_send_nps_survey_data',
            nps_rate: nps_rate,
            nps_from: nps_from,
            nonce: two_speed.nonce,
        }
    }).success(function ($result) {
        console.log($result);
    });
}