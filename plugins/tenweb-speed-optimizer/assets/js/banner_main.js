jQuery(document).ready(function (){
    jQuery('.two-banner-close-button').on('click', function(e) {
        e.preventDefault();
        two_close_banner();
    });
});

function two_close_banner() {
    jQuery('.two-banner-main-container').addClass('two-hidden');
}
