if( ViPscw === undefined ) {
    var ViPscw = {};
}

jQuery( document ).ready( function ( $ ) {
    'use strict';


    ViPscw.SetupWizard = {
        init() {
            const container = $( ".pscw-setup-wizard-container" );
            this.load();
            container.on( "click", this.click.bind(this) );
        },

        load() {
            $( "#pscw-setup-select-position" ).dropdown();
            $( ".vi-ui.checkbox" ).checkbox();
        },

        click(e) {
            let selectedEle = e.target;
            switch (selectedEle.id) {
                case 'pscw-setup-finished':
                    let data = {}, selectedSizeCharts = [];

                    $('input[name="pscw-selected-size-chart"]').each( function () {
                        if ( $( this ).prop( 'checked') ) {
                            selectedSizeCharts.push( $(this).val() );
                        }
                    } );
                    data.action = 'pscw_setup_wizard'
                    data.selected_position = $( "#pscw-setup-select-position" ).val();
                    data.selected_size_charts = selectedSizeCharts;
                    data.nonce = VicPscwParams.nonce;

                    $.ajax({
                        url: VicPscwParams.ajaxUrl,
                        type: 'post',
                        dataType: 'json',
                        data: data,
                        beforeSend: function () {
                            $( selectedEle ).addClass( 'loading' );
                        },
                        success: function (res) {
                            if ( res?.success ) {
                                if ( res?.data.productLink ) {
                                    location.href = res?.data.productLink;
                                }
                            }
                        },
                        error: function (res) {
                            console.log( res?.data );
                        },
                        complete: function () {}
                    });
                    break;
                case 'pscw-setup-next':
                    let segmentContainer = $( '.pscw-segment-container' ),
                        stepEle = $( '.vi-ui.steps' ).find( '.step' );
                    segmentContainer.first().removeClass( "active" );
                    segmentContainer.last().addClass( "active" );
                    stepEle.first().removeClass( "active" );
                    stepEle.last().addClass( "active" );
                    break;
            }
        }
    }

    ViPscw.SetupWizard.init();
} );