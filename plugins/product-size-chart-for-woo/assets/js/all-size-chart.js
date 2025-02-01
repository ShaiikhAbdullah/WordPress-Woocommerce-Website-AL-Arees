jQuery(document).ready(function ($) {
    "use strict";
    $('.woo_sc_short_code').on('click', function () {
        $(this).select();
        document.execCommand('copy');
    });
    $('.woo_sc_short_code').on('click', function () {
        let _this = $(this).parent().find('.woo_sc_copied');
        _this.css('visibility', 'visible');
        setTimeout(function () {
            _this.css('visibility', 'hidden');
        }, 1000);
    });
    $('#pscw_migrate_all_data').on('click', function() {
        const $this = $(this);
        const message = 'This action will reconvert the size chart table from version 1.x to 2.x. All size charts created after version 2.x will not be affected.';
        if (confirm(message)) {
            $.ajax({
                url: VicPscwParams.ajaxUrl,
                type: 'post',
                data: {
                    action: 'pscw_migrate_data',
                    nonce: VicPscwParams.nonce,
                },
                beforeSend: function () {
                    $this.addClass('pscw_loading_button');
                },
                success: function (res) {
                    console.log('Success:', res);
                },
                error: function (res) {
                    console.log('Error:', res?.data);
                },
                complete: function () {
                    $this.removeClass('pscw_loading_button');
                }
            });
        }
    })

});