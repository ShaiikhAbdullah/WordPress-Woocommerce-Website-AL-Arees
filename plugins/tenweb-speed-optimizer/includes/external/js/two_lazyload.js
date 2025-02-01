jQuery("document").ready(function () {
    jQuery("*:not('br, hr, iframe, pre')").each(function () {
        let bg_image_data = get_bg_images(this);
        if (bg_image_data) {
            jQuery(this).addClass("two_bg");
            jQuery(this).attr("data-src", bg_image_data['first_bg_image']);
            jQuery(this).attr("data-full-bg-image", bg_image_data['bg_image_property']);
            jQuery(this).on('visibility', function () {
                var $element = jQuery(this);
                setInterval(function () {
                    jQuery('.two_bg').Lazy({
                        visibleOnly: true,
                    });
                }, 300);
            }).trigger('visibility');
        }
    });

    jQuery('.two_bg').Lazy({
        visibleOnly: true,
    });

    function get_bg_images(elem) {
        let style = elem.currentStyle || window.getComputedStyle(elem, false);
        let bg_image = style.backgroundImage;

        if (bg_image === 'none' || bg_image.indexOf(window['two_svg_placeholder']) === -1) {
            return;
        }

        bg_image = bg_image.replace(window['two_svg_placeholder'], "");
        if (!bg_image) {
            return;
        }

        let first_bg_image = bg_image.slice(5, bg_image.length).split('")')[0];
        return {
            "first_bg_image": first_bg_image,
            "bg_image_property": bg_image
        }

    }

});

