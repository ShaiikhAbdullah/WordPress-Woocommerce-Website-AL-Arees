jQuery(document).ready( function ( $ ) {
    "use strict";
    const pscwMode = $( 'input[name="pscw_mode"]:checked' );
    function handlePscwMode( value ) {
        let overrideActive = $( "#pscw_override.active" );
        if ( value === 'override' ) {
            $( "#pscw_override" ).addClass( "active" );
            $( "#pscw_override.active" ).select2( {
                width: '100%',
                minimumInputLength: 1,
                placeholder: 'Size chart...',
                allowClear: true,
                ajax: {
                    type: 'post',
                    url: VicPscwParams.ajaxUrl,
                    data: function (params) {
                        let query = {
                            key_search: params.term,
                            action: 'pscw_search_size_chart',
                            nonce: VicPscwParams.nonce,
                        };
                        return query;
                    },
                    processResults: function (data) {
                        return data ? data : {results: []};
                    }
                }
            } );
        }else {
            overrideActive.select2( "destroy" );
            overrideActive.removeClass( "active" );
        }
    }

    if ( pscwMode.val() === 'override' ) {
        handlePscwMode( pscwMode.val() );
    }

    $( 'input[name="pscw_mode"]' ).on( "change", function () {
        handlePscwMode( $(this).val() );
    } );
} );