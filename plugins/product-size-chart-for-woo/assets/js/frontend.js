if( ViPscw === undefined ) {
    var ViPscw = {};
}

jQuery(document).ready(function ($) {
    "use strict";

    ViPscw.FrontEnd = {
        init() {
            this.load();
        },

        load() {
            if ( ! ViPscwFontParams.isCustomize || ! ViPscwFontParams.pscwSizeChartMode ) {
                let htmlbody = $('html, body');
                htmlbody.on('click', '.woo_sc_call_popup', function () {
                    htmlbody.find("#woo_sc_modal").show();
                    htmlbody.css({
                        overflow: 'hidden',
                        height: '100%'
                    });
                });

                htmlbody.on('click', '.woo_sc_modal_close', function () {
                    htmlbody.find("#woo_sc_modal").hide();
                    htmlbody.css({
                        overflow: 'auto',
                        height: 'auto'
                    });
                });
                htmlbody.on('click', '#woo_sc_modal', function (event) {
                    if (!event.target.closest('.woo_sc_modal_content')) {
                        htmlbody.find("#woo_sc_modal").hide();
                        htmlbody.css({
                            overflow: 'auto',
                            height: 'auto'
                        });
                    }
                });
            }
        },
    }

    ViPscw.FrontEnd.init();

});
