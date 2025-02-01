jQuery(document).ready(function() {

    jQuery('.two-referral-copy').on('click', function() {
        copyText( jQuery(this), '#two-referral-link' );
    });
    jQuery('.two-referral-copy-topbar').on('click', function() {
        copyText( jQuery(this), '#two-referral-link-topbar' );
    });

});

function copyText(el, id){
    if (!jQuery(el).hasClass('copied')) {
        setTimeout(function() {
            jQuery(el).removeClass('copied');
            jQuery(el).text('Copy');
        }, 1000);
        copyToClipboard(jQuery(id));
        jQuery(el).addClass('copied');
        jQuery(el).text('Copied');
    }
    return false;
}

function copyToClipboard(element) {
    var $temp = jQuery("<input>");
    jQuery("body").append($temp);
    $temp.val(jQuery(element).text()).select();
    document.execCommand("copy");
    $temp.remove();
}