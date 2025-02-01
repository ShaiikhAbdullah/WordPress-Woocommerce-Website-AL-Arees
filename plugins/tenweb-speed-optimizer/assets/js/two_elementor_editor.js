const elementorConfigsToRecreateCCSS = [
    'background_image',
    '_background_image',
    'background_color',
    '_background_color',
];
jQuery(document).on('click', '#elementor-panel-saver-button-publish',function(){
    let page_id = '';
    if ( typeof ElementorConfig !== "undefined" ) {
        page_id = ElementorConfig.initial_document.id;
    }
    if ( getLocalStorageWithExpiry('regenerateCCSSFor_' + page_id ) === "1" ) {
        jQuery.ajax({
            type: "POST",
            url: two_elementor_vars.ajax_url,
            dataType: 'json',
            data: {
                action: "two_elementor_regenerate_ccss",
                page_id: page_id,
                //devide all types by comma ','
                allowed_post_types: 'page',
                nonce: two_elementor_vars.nonce,
            }
        }).success(function () {
            localStorage.removeItem('regenerateCCSSFor_' + page_id );
        });
    }
});
jQuery(window).on('elementor:init', function () {
    elementor.channels.editor.on('change', function (t) {
        let regenerateCCSS = getLocalStorageWithExpiry('regenerateCCSSFor_' + t.options.container.document.id);
        if (regenerateCCSS !== "1" ) {
            elementorConfigsToRecreateCCSS.forEach(function (setting) {
                if (typeof t.options.container.oldValues[setting] !== "undefined") {
                    setLocalStorageWithExpiry('regenerateCCSSFor_' + t.options.container.document.id, "1", 86400);
                    return false;
                }
            });
        }
    });
});

function setLocalStorageWithExpiry(key, value, ttl) {
    const now = new Date();

    const item = {
        value: value,
        expiry: now.getTime() + ttl,
    }
    localStorage.setItem(key, JSON.stringify(item));
}

function getLocalStorageWithExpiry(key) {
    const itemStr = localStorage.getItem(key);
    if (!itemStr) {
        return null;
    }
    const item = JSON.parse(itemStr);
    const now = new Date();
    if (now.getTime() > item.expiry) {
        localStorage.removeItem(key);
        return null;
    }
    return item.value;
}
