var finishedDate = new Date("Nov 29, 2022 00:00:00");//hour-4 our timezone
// var timezoneDiff = countDownDate.getTimezoneOffset() * 60000;
// var finishedDate = new Date(countDownDate.getTime() - timezoneDiff );

var x = setInterval(function() {

    var distance = finishedDate - new Date();

    var days = Math.floor(distance / (1000 * 60 * 60 * 24));
    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    if (typeof days == 'number') {
        jQuery(".two_bfc_days .two_black_friday_countdown_each:nth-child(1)").html(days.toString().padStart(2, '0')[0]);
        jQuery(".two_bfc_days .two_black_friday_countdown_each:nth-child(2)").html(days.toString().padStart(2, '0')[1]);
    }
    if (typeof hours == 'number') {
        jQuery(".two_bfc_hours .two_black_friday_countdown_each:nth-child(1)").html(hours.toString().padStart(2, '0')[0]);
        jQuery(".two_bfc_hours .two_black_friday_countdown_each:nth-child(2)").html(hours.toString().padStart(2, '0')[1]);
    }
    if (typeof minutes == 'number') {
        jQuery(".two_bfc_minutes .two_black_friday_countdown_each:nth-child(1)").html(minutes.toString().padStart(2, '0')[0]);
        jQuery(".two_bfc_minutes .two_black_friday_countdown_each:nth-child(2)").html(minutes.toString().padStart(2, '0')[1]);
    }
}, 1000);