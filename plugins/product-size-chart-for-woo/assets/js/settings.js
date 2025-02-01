jQuery( document ).ready( function( $ ) {
    "use strict";
    /*Setting Field Rules*/
    const label = $('.woo_sc_sc_label'),
          scPosition = $('#woo_cs_select_position'),
          scButtonType = $('#woo_sc_type_btn'),
          scIcon = $('.woo_sc_sc_icon');

    $('.dropdown').dropdown();

    const handleShowFieldsBasedOnButtonType = () => {
        switch ( scButtonType.val() ) {
            case 'text':
                label.show();
                scIcon.hide();
                break;
            case 'icon_text':
            case 'icon':
                label.hide();
                scIcon.show();
                break;
        }
    };
    const handleShowFieldsBasedOnSizeChartType = () => {
        const scMulti = $('.woo_sc_multi'),
              scBtnPopupPosition = $('.woo_sc_btn_popup_position'),
              scBtnColor = $('.woo_sc_btn_color'),
              scGetShortCode = $('.get_short_code'),
              scBtnType = $('.woo_sc_btn_type');

        switch ( scPosition.val() ) {
            case 'before_add_to_cart':
            case 'after_add_to_cart':
                scMulti.hide();
                scBtnPopupPosition.hide();
                scBtnColor.show();
                scGetShortCode.hide();
                scBtnType.show();
                handleShowFieldsBasedOnButtonType();
                break;
            case 'pop-up':
                scMulti.hide();
                scBtnPopupPosition.show();
                scGetShortCode.hide();
                scBtnColor.show();
                scBtnType.show();
                handleShowFieldsBasedOnButtonType();
                break;
            case 'product_tabs':
                scMulti.show();
                scBtnPopupPosition.hide();
                scGetShortCode.hide();
                scBtnType.hide();
                scBtnColor.hide();
                label.show();
                scIcon.hide();
                break;
            case 'none':
                scMulti.hide();
                scBtnPopupPosition.hide();
                scGetShortCode.show();
                scBtnType.hide();
                scBtnColor.hide();
                label.hide();
                scIcon.hide();
                break;
        }
    };

    handleShowFieldsBasedOnButtonType();
    handleShowFieldsBasedOnSizeChartType();

    scPosition.on('change', function () {
        handleShowFieldsBasedOnSizeChartType();
    });
    scButtonType.on('change', function () {
        handleShowFieldsBasedOnButtonType();
    });


    /*Color picker*/
    const colorPicker = $('.color-picker');
    if (colorPicker.length !== 0) {
        colorPicker.iris({
            change: function (event, ui) {
                $(this).parent().find('.color-picker').css({backgroundColor: ui.color.toString()});
            },
            hide: true,
            border: true
        }).on( 'click', function () {
            $('.iris-picker').hide();
            $(this).closest('td').find('.iris-picker').show();
        });

        $('body').on( 'click', function () {
            $('.iris-picker').hide();
        });
        colorPicker.on( 'click', function (event) {
            event.stopPropagation();
        });
    }


});