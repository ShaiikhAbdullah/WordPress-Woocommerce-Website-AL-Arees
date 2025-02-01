/* Do request to get optimized images count */
jQuery.ajax({
  type: 'POST',
  url: two_speed.ajax_url,
  dataType: 'json',
  data: {
    action: "two_get_optimized_images",
    nonce: two_speed.nonce,
  }
}).success(function(res){
  let total_images_count, optimized_images_count;
  if( typeof res['data'] != 'undefined' ) {
    total_images_count = res['data']['total_images_count'];
    optimized_images_count = res['data']['optimized_images_count']
    jQuery('.two-adminBar.two_empty_images_count').text(optimized_images_count + ' of ' + total_images_count);
    jQuery('.two-settings-basic.two_empty_images_count').text(optimized_images_count);
  }
});

/* Keeping time interval to check page optimized or not every 3 min*/
var two_is_page_optimized_interval;
var reanalyzing_status;
jQuery(document).ready(function () {
  if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
    jQuery('body').addClass('two_elementor_dark');
  }

  let link_exceed = '', limit_exceed_content = '';
  if (two_speed.optimize_entire_website != false && jQuery('#wpcontent').length > 0 && typeof two_limit_exceed_popup_content !== 'undefined' ) {
    link_exceed = two_limit_exceed_popup_content.exceed_url;
    limit_exceed_content = '<div class="two-limit-exceed-main-container">' +
        '<div class="two-limit-exceed-container">' +
        '<span class="two-limit-exceed-close" onclick="two_close_limit_exceed()"></span>' +
        '<p class="two-limit-exceed-title">' + two_limit_exceed_popup_content.exceed_title + '</p>' +
        '<p class="two-limit-exceed-description">' + two_limit_exceed_popup_content.exceed_desc_1 + '<br>'
            + two_limit_exceed_popup_content.exceed_desc_2 +
        '</p>' +
        '<a class="two-limit-exceed-button" href="' + link_exceed + '">' + two_limit_exceed_popup_content.exceed_button_text
            + '</a>' +
        '</div>' +
        '</div>' +
        '</div>';
    jQuery('#wpcontent').append(limit_exceed_content);
  }

  /* Do request to get reanalyzing_status updated*/
  let posts = [];
  jQuery('.two-no-scores').each( function() {
    if (!posts.includes(jQuery(this)) && jQuery(this).data('no-score-for') != '' ) {
      posts.push(jQuery(this).data('no-score-for'));
    }
  });
  let unique_posts = [...new Set(posts)];
  if ( unique_posts.length > 0 ) {
    reanalyzing_status = setInterval(function () {
      jQuery.each(unique_posts,function( index, value ){
        let post_id = value;
        jQuery.ajax({
          type: "POST",
          url: two_speed.ajax_url,
          dataType: 'json',
          data: {
            action: "two_get_page_score",
            post_id: post_id,
            nonce: two_speed.nonce,
          }
        }).success(function (results) {
          let score_data = results['data'];
          if (score_data && score_data['previous_score'] && score_data['previous_score']['status'] != 'inprogress'
              && score_data['current_score'] && score_data['current_score']['status'] != 'inprogress') {
            if (jQuery('.two_reanalyze_button').data('from-wp-admin') == 1
                || jQuery('.two_reanalyze_link').data('from-wp-admin') == 1 ) {
              after_reanalyze_admin_bar(results,post_id);
            } else if (jQuery('.two_reanalyze_button').data('from-elementor') == 1
                || jQuery('.two_reanalyze_link').data('from-elementor') == 1 ) {
              after_reanalyze_elementor(results);
            }
            clearInterval(reanalyzing_status);
          }
        });
      });

    }, 10000);
  }
  /* Add an action to check a page score.*/
  jQuery(".two-notoptimized a").on("click", function () {
    if ( !jQuery(".two_ongoing_optimization").length > 0 ) {
      if (typeof jQuery(this).attr("href") != 'undefined') {
        return;
      }
      if (two_speed.optimize_entire_website != false) {
        two_limit_exceed_popup();
      } else {
        two_optimize_page(this);
      }
    } else {
      jQuery(".two-optimization-in-progress-tooltip").addClass("two-hidden");
      jQuery(this).next(".two-optimization-in-progress-tooltip").removeClass("two-hidden");
    }
  });

  jQuery(".two-optimization-in-progress-close").on("click",function(){
    jQuery(".two-optimization-in-progress-tooltip").addClass("two-hidden");
  });

  if ( jQuery('.two_admin_bar_menu_main_notif').length > 0 ) {
    jQuery('.two_admin_bar_notif_menu').addClass('two_pages_optimizing');
    if ( jQuery('.two_admin_bar_menu_content.two_optimized').length > 0 ) {
      jQuery('.two_admin_bar_notif_menu .two_admin_bar_menu_header span').addClass('two_green_info');
    }
  }

  jQuery("#two_optimize_now_button").on("click", function () {
    two_optimize_page(this);
    /* Run ajax every 3 min to check if page optimized */
    two_is_page_optimized_interval = setInterval( two_is_page_optimized, 180000, this );
  });

  /* Add a hover action to show scores.*/
  jQuery(".two-optimized .two-optimized-see-more").mouseenter(function () {
      jQuery(this).parent().parent().find(".two-score-container").removeClass("two-hidden");
    }).mouseleave( function () {
      jQuery(this).parent().parent().find(".two-score-container").addClass("two-hidden");
    });
  /* Draw circle on given scores.*/
  jQuery('.two-score-circle').each(function () {
    two_draw_score_circle(this);
  });

  /* Show/hide Image optimizer menu content container */
  jQuery("#wp-admin-bar-two_adminbar_info").mouseenter(function(){
      jQuery(".two_admin_bar_menu_main").removeClass("two_hidden");
  }).mouseleave(function() {
      jQuery(".two_admin_bar_menu_main").addClass("two_hidden");
  });

  /* Show/hide Booster optimizer notif content container */
  if ( jQuery(".two_admin_bar_menu_main_notif").hasClass("two_hidden") ) {
    jQuery("#wp-admin-bar-two_adminbar_progress_info").mouseenter(function () {
      jQuery(".two_admin_bar_menu_main_notif").removeClass("two_hidden");
    }).mouseleave(function () {
      jQuery(".two_admin_bar_menu_main_notif").addClass("two_hidden");
    });
  }

  jQuery(".two_admin_bar_menu_main_notif_optimized_close").on("click", function() {
    jQuery(this).parent().remove();
    if ( !jQuery('.two_admin_bar_menu_main_notif').children().length > 0 ) {
      jQuery('#wp-admin-bar-two_adminbar_progress_info').remove();
    }
    var post_id = jQuery(this).data("post_id");
    jQuery.ajax({
      url: two_speed.ajax_url,
      type: "POST",
      data: {
        action: "two_optimized_notif_closed",
        post_id: post_id,
        nonce: two_speed.nonce
      },
      success: function (result) {
        /* Show/hide Booster optimizer notif content container */
        if ( jQuery(".two_admin_bar_menu_main_notif").hasClass("two_hidden") ) {
          jQuery("#wp-admin-bar-two_adminbar_progress_info").mouseenter(function () {
            jQuery(".two_admin_bar_menu_main_notif").removeClass("two_hidden");
          }).mouseleave(function () {
            jQuery(".two_admin_bar_menu_main_notif").addClass("two_hidden");
          });
        }
        console.log(result);
      }
    });
  });

  jQuery(".two_clear_cache").on("click", function (e) {
    e.preventDefault();
    two_clear_cache(this);
  });

  jQuery(".two_optimized_cont").on("click", function() {
    if( jQuery(this).find(".two_arrow").hasClass("two_up_arrow") ) {
      jQuery(this).find(".two_score_block_container").addClass("two_hidden");
      jQuery(this).find(".two_arrow").addClass("two_down_arrow").removeClass("two_up_arrow");
    } else {
      jQuery(".two_score_block_container").addClass("two_hidden");
      jQuery(".two_optimized_congrats_row .two_arrow").addClass("two_down_arrow").removeClass("two_up_arrow");
      jQuery(this).find(".two_score_block_container").removeClass("two_hidden");
      jQuery(this).find(".two_arrow").addClass("two_up_arrow").removeClass("two_down_arrow");
    }
  });

  jQuery('.two-faq-item').on('click', function () {
    jQuery(this).toggleClass('active');
  });
  jQuery('.two-disconnect-link a').on('click', function () {
    jQuery('.two-disconnect-popup').appendTo('body').addClass('open');
    return false;
  });
  jQuery('.two-button-cancel, .two-close-img').on('click', function () {
    jQuery('.two-disconnect-popup').removeClass('open');
    return false;
  });

  jQuery('.two-open-contact-care-team').on('click', function(e) {
    e.preventDefault();
    jQuery('.two_admin_bar_menu_main').trigger('mouseleave');
    jQuery('.two-contact-care-popup-main').removeClass('two-hidden');
    jQuery('body').addClass('two-overflow-hidden');
  });
  jQuery('.two-contact-care-close').on('click', function() {
    jQuery('.two-contact-care-popup-main').addClass('two-hidden');
    jQuery('body').removeClass('two-overflow-hidden');
  });

  jQuery('.two-button-io-active-optimize').on('click',function(e) {
    e.preventDefault();
    if ( !jQuery(this).hasClass('two-button-io-disable') ) {
      jQuery(this).addClass('two-button-io-disable');
      jQuery(this).text('');
      jQuery(this).css('opacity','0.8');
      jQuery(this).append("<span class='two-loading'></span>");
    }
    jQuery.ajax({
      type: 'POST',
      url: two_speed.ajax_url,
      dataType: 'json',
      data: {
        action: "two_setFlowIdNotificationId",
        nonce: two_speed.nonce,
      }
    }).success(function(res){
      window.location.href = res.data;
    });
  });
});

function two_limit_exceed_popup() {
  jQuery('.two-limit-exceed-main-container').css('display','flex');
};

function two_close_limit_exceed() {
  jQuery('.two-limit-exceed-main-container').css('display', 'none');
}

/* Recount google speed score */
function two_clear_cache(that) {
  let clear_cache_from = '';
  if ( jQuery(that).hasClass('two_cache_button') ) {
    jQuery(that).text('');
    jQuery(that).css('opacity','0.8');
    jQuery(that).append("<span class='two-loading'></span>");
  } else {
    jQuery(that).text( two_speed.clearing );
    jQuery(that).prepend("<span class='two_cache_clearing'></span>");
  }
  if ( typeof jQuery(that).data('from') !== undefined ) {
    clear_cache_from = jQuery(that).data('from');
  }
  jQuery.ajax({
    type: "POST",
    url: ajaxurl,
    dataType: 'json',
    data: {
      action: "two_settings",
      task: "clear_cache",
      clear_cache_from: clear_cache_from,
      nonce: two_speed.nonce,
    }
  }).done(function (data) {
    if ( jQuery(that).hasClass('two_cache_button') ) {
      jQuery(that).css('opacity','1');
      jQuery(that).closest('.two-loading').remove();
      jQuery(that).text(two_speed.clear);
    } else {
      jQuery(".two_cache_clearing").remove();
      if (data.success) {
        jQuery(that).text(two_speed.cleared);
        jQuery(that).addClass("two_cache_cleared");
      } else {
        jQuery(that).text(two_speed.clear);
      }
    }
  }).error(function (data) {
    if ( jQuery(that).hasClass('two_cache_button') ) {
      jQuery(that).css('opacity','1');
      jQuery(that).closest('.two-loading').remove();
      jQuery(that).text(two_speed.clear);
    } else {
      jQuery(".two_cache_clearing").remove();
      jQuery(that).text(two_speed.clear);
    }
  });
}

/* Checking is page optimized */
function two_is_page_optimized( that ) {
  var post_id = jQuery(that).data("post-id");
  jQuery.ajax({
    url: two_speed.ajax_url,
    type: "POST",
    data: {
      action: "two_is_page_optimized",
      post_id: post_id,
      nonce: two_speed.nonce
    },
    success: function (result) {
      if ( result['success'] ) {
        clearInterval(two_is_page_optimized_interval);
        jQuery(".two_in_progress_cont").remove();
        after_reanalyze_admin_bar(result,post_id);
        jQuery(".two_empty_front_optimized_content").removeClass("two_hidden");
      }
    },
    error: function () {},
  });
}

/**
 * Optimize the page.
 * @param that
 */
function two_optimize_page(that) {
  if ( !jQuery(that).hasClass('two-button-disabled') ) {
    var post_id = jQuery(that).data("post-id");
    var initiator = jQuery(that).data("initiator");
    if (jQuery(that).attr('id') == 'two_optimize_now_button') {
      jQuery(".two_admin_bar_menu_header.two_frontpage_not_optimized img, .two_admin_bar_menu_header.two_frontpage_optimized img").hide();
      jQuery(".two_admin_bar_menu_header.two_frontpage_not_optimized,.two_admin_bar_menu_header.two_frontpage_optimized").removeClass("two_frontpage_not_optimized").addClass("two_frontpage_optimizing");
      jQuery(".two_frontpage_optimizing span").removeClass('two_hidden');
      var two_in_progress_cont = jQuery(".two_in_progress_cont").html();
      jQuery(".two_admin_bar_menu_content.two_not_optimized_content, .two_optimized").empty().append(two_in_progress_cont).addClass("two_in_progress_cont");
    } else if (jQuery(that).hasClass("two_optimize_button_elementor")) {
      jQuery(".elementor-control-title, .two_elementor_control_container").addClass("two-hidden");
      jQuery(".two-score-section,.two-elementor-container-title").addClass("two-hidden");
      jQuery(".two_elementor_settings_content").addClass("two-optimizing");
      jQuery(".two-page-speed.two-optimizing").removeClass("two-hidden");
    } else {
      var parent = jQuery(that).parent().parent();
      parent.find(".two-optimizing").removeClass("two-hidden");
      parent.find(".two-optimizing").addClass("two_ongoing_optimization");
      parent.find(".two-notoptimized").addClass("two-hidden");
    }
    jQuery.ajax({
      url: two_speed.ajax_url,
      type: "GET",
      data: {
        action: "two_optimize_page",
        post_id: post_id,
        initiator: initiator,
        nonce: two_speed.nonce
      },
      success: function (result) {
      },
      error: function (xhr, ajaxOptions, thrownError) {
        clearInterval(two_is_page_optimized_interval);
      },
    });
  }
}

function two_reanalyze_score(that){
  if ( !jQuery(that).hasClass('two-button-disabled') ) {
    var post_id = jQuery(that).data("post_id");
    jQuery('.two-pages-list-reanalyzing[data-post-id="' + post_id + '"]').removeClass("two-hidden");
    jQuery('.two-optimized-see-more[data-post-id="' + post_id + '"]').addClass("two-hidden");
    let reanalyzing_container = jQuery('.two-any-reanalyzing-score-section[data-id="' + post_id + '"]');
    let reanalyze_score_for;
    if ( reanalyzing_container.find(".two-score-container-both .two-no-scores").length > 1
      || reanalyzing_container.find(".two_score_container_both.two-no-scores").length > 1 ) {
      reanalyze_score_for = 'both';
      reanalyzing_container.find(".two-score-container-both .two_reanalyze_link").addClass("two-hidden");
      reanalyzing_container.find(".two_score_container_both .two_reanalyze_link").addClass("two-hidden");
      reanalyzing_container.find(".two_score_container_both .two_reanalyze_link").next(".two-page-speed.two-optimizing").removeClass("two-hidden");
      reanalyzing_container.find(".two-score-container-both .two_reanalyze_link").next(".two-page-speed.two-optimizing").removeClass("two-hidden");
      reanalyzing_container.find(".two_reanalyze_container .two-page-speed.two-optimizing").removeClass("two-hidden");
      reanalyzing_container.find(".two_reanalyze_button").text("Reanalyzing...");
    } else {
      if (jQuery(that).parent().hasClass("two-new-scores") || jQuery(that).hasClass("two_reanalyze_button")) {
        reanalyze_score_for = 'new';
        reanalyzing_container.find(".two_reanalyze_container .two-page-speed.two-optimizing").removeClass("two-hidden");
        reanalyzing_container.find(".two_reanalyze_button").text("Reanalyzing...");
      } else if (jQuery(that).parent().hasClass("two-old-scores")) {
        reanalyze_score_for = 'old';
      }
      if (jQuery(that).hasClass("two_reanalyze_button")) {
        reanalyzing_container.find(".two-new-scores").addClass("two-no-scores");
        reanalyzing_container.find(".two-new-scores .two_reanalyze_link").addClass("two-hidden");
        reanalyzing_container.find(".two-new-scores .two_reanalyze_link").next(".two-page-speed.two-optimizing").removeClass("two-hidden");
      } else {
        jQuery(that).addClass("two-hidden");
        jQuery(that).find(".two-page-speed.two-optimizing").removeClass("two-hidden");
      }
    }
    reanalyzing_container.find(".two_elementor_control_container .two_optimize_button_elementor.two_optimize_button").addClass("two-button-disabled");
    reanalyzing_container.find("#two_optimize_now_button").addClass("two-button-disabled");
    reanalyzing_container.find(".two_reanalyze_button").addClass("two-button-disabled");

    jQuery.ajax({
      type: "POST",
      url: two_speed.ajax_url,
      dataType: 'json',
      data: {
        action: "two_recount_score",
        reanalyze_score_for: reanalyze_score_for,
        post_id: post_id,
        nonce: two_speed.nonce,
      }
    }).success(function (results) {
      if ( jQuery('.two_reanalyze_button').data('from-wp-admin') == 1
          || jQuery('.two_reanalyze_link').data('from-wp-admin') == 1) {
        after_reanalyze_admin_bar(results,post_id);
      } else if ( jQuery('.two_reanalyze_button').data('from-elementor') == 1
          ||  jQuery('.two_reanalyze_link').data('from-elementor') == 1) {
        after_reanalyze_elementor(results);
      }
    }).error(function (data) {
      console.log('error');
    });
  }
}

/**
 * Draw circle on given score.
 * @param that
 */
function two_draw_score_circle(that) {
  var score = parseInt(jQuery(that).data('score'));
  var size = parseInt(jQuery(that).data('size'));
  var thickness = parseInt(jQuery(that).data('thickness'));
  var color = score <= 49 ? "rgb(253, 60, 49)" : (score >= 90 ? "rgb(12, 206, 107)" : "rgb(255, 164, 0)");
  var background_color = score <= 49 ? "#FD3C311A" : (score >= 90 ? "#22B3391A" : "#fd3c311a");
  if ( jQuery(that).hasClass('two_circle_with_bg') ) {
    jQuery(that).css('background-color',background_color);
  }
  jQuery(that).parent().find('.two-load-time').html(jQuery(that).data('loading-time'));
  var _this = that;
  jQuery(_this).circleProgress({
    value: score / 100,
    size: size,
    startAngle: -Math.PI / 4 * 2,
    lineCap: 'round',
    emptyFill: "rgba(255, 255, 255, 0)",
    thickness: thickness,
    fill: {
      color: color
    }
  }).on('circle-animation-progress', function (event, progress) {
    if (score != 0) {
      content = Math.round(score * progress);
      jQuery(that).find('.two-score-circle-animated').html(content).css({"color": color});
      jQuery(that).find('canvas').html(Math.round(score * progress));
    }
  });
}

/* Adding button in Elementor edit panel navigation view */
function two_add_elementor_button() {
  window.elementor.modules.layouts.panel.pages.menu.Menu.addItem({
    name: two_speed.title,
    icon: "two-element-menu-icon",
    title: two_speed.title,
    type: "page",
    callback: () => {
      try {
        window.$e.route("panel/page-settings/two_optimize")
      } catch (e) {
        window.$e.route("panel/page-settings/settings"), window.$e.route("panel/page-settings/two_optimize")
      }
    }
  }, "more")
}
/* show 10web Booster button in sidebar only for pages and posts */
if ( (two_speed.post_type == 'page' || two_speed.post_type == 'post') && two_speed.post_status == 'publish'
    && two_speed.post_optimizable ) {
  jQuery(window).on("elementor:init", () => {
    window.elementor.on("panel:init", () => {
      setTimeout(two_add_elementor_button)
    })
  });
}

function after_reanalyze_elementor(results) {
  clearInterval(reanalyzing_status);
  let data;
  if( typeof results['data'] !== 'undefined' && typeof results['data']['previous_score'] !== 'undefined' ) {
    data = results['data'];
    jQuery(".two-old-scores").removeClass("two-no-scores");
    // /* TODO check this */
    // /* setting attr("data") is for case when no any score are set yet  empty case */
    // jQuery(".two-old-scores .two-score-mobile .two-score-circle").attr("data-score", data["previous_score"]["mobile_score"]);
    // jQuery(".two-old-scores .two-score-mobile .two-score-circle").attr("data-loading-time", data["previous_score"]["mobile_tti"]);
    // jQuery(".two-old-scores .two-score-desktop .two-score-circle").attr("data-score", data["previous_score"]["desktop_score"]);
    // jQuery(".two-old-scores .two-score-desktop .two-score-circle").attr("data-loading-time", data["previous_score"]["desktop_tti"]);

    /* setting data(score) is for case when need to change one time rendered element attr */
    jQuery(".two-old-scores .two-score-mobile .two-score-circle").data("score", data["previous_score"]["mobile_score"]);
    jQuery(".two-old-scores .two-score-mobile .two-score-circle").data("loading-time", data["previous_score"]["mobile_tti"]);
    jQuery(".two-old-scores .two-score-desktop .two-score-circle").data("score", data["previous_score"]["desktop_score"]);
    jQuery(".two-old-scores .two-score-desktop .two-score-circle").data("loading-time", data["previous_score"]["desktop_tti"]);
    jQuery('.two-old-scores .two-score-circle').each(function() {
      two_draw_score_circle(this);
    });
  }
  if( typeof results['data'] !== 'undefined' && typeof results['data']['current_score'] !== 'undefined' ) {
    data = results['data'];
    jQuery(".two-new-scores").removeClass("two-no-scores");
    // /* setting attr("data") is for case when no any score are set yet  empty case */
    // jQuery(".two-new-scores .two-score-mobile .two-score-circle").attr("data-score", data["current_score"]["mobile_score"]);
    // jQuery(".two-new-scores .two-score-mobile .two-score-circle").attr("data-loading-time", data["current_score"]["mobile_tti"]);
    // jQuery(".two-new-scores .two-score-desktop .two-score-circle").attr("data-score", data["current_score"]["desktop_score"]);
    // jQuery(".two-new-scores .two-score-desktop .two-score-circle").attr("data-loading-time", data["current_score"]["desktop_tti"]);

    /* setting data(score) is for case when need to change one time rendered element attr */
    jQuery(".two-new-scores .two-score-mobile .two-score-circle").data("score", data["current_score"]["mobile_score"]);
    jQuery(".two-new-scores .two-score-mobile .two-score-circle").data("loading-time", data["current_score"]["mobile_tti"]);
    jQuery(".two-new-scores .two-score-desktop .two-score-circle").data("score", data["current_score"]["desktop_score"]);
    jQuery(".two-new-scores .two-score-desktop .two-score-circle").data("loading-time", data["current_score"]["desktop_tti"]);
    jQuery('.two-new-scores .two-score-circle').each(function() {
      two_draw_score_circle(this);
    });
    jQuery(".two_reanalyze_container .two-page-speed.two-optimizing").addClass("two-hidden");
    jQuery(".two_reanalyze_button").text("Reanalyze");
  }
  jQuery(".two-score-container-both .two_reanalyze_link").next(".two-page-speed.two-optimizing").addClass("two-hidden");
  jQuery(".two_elementor_control_container .two_optimize_button_elementor.two_optimize_button").removeClass("two-button-disabled");
  jQuery(".two_reanalyze_button").removeClass("two-button-disabled");
}
function after_reanalyze_admin_bar(results,post_id) {
  clearInterval(reanalyzing_status);
  var data;
  let the_score_container = jQuery('.two-any-reanalyzing-score-section[data-id="' + post_id + '"]');
  if( typeof results['data'] !== 'undefined' && typeof results['data']['previous_score'] !== 'undefined'
  && typeof results['data']['previous_score']['desktop_score'] !== 'undefined' ) {
    the_score_container.find(".two-old-scores").removeClass("two-no-scores");
    data = results['data'];
    the_score_container.find(".two_score_container.two_score_container_mobile_old .two-score-circle").data("score", data["previous_score"]["mobile_score"]);
    the_score_container.find(".two_score_container.two_score_container_mobile_old .two_load_time").text(data["previous_score"]["mobile_tti"] + 's');
    the_score_container.find(".two_score_container.two_score_container_desktop_old .two-score-circle").data("score", data["previous_score"]["desktop_score"]);
    the_score_container.find(".two_score_container.two_score_container_desktop_old .two_load_time").text(data["previous_score"]["desktop_tti"] + 's');
    the_score_container.find('.two-old-scores .two-score-circle').each(function() {
      two_draw_score_circle(this);
    });
  }
  if( typeof results['data'] !== 'undefined' && typeof results['data']['current_score'] !== 'undefined'
    && typeof results['data']['current_score']['desktop_score'] !== 'undefined' ) {
    the_score_container.find(".two-new-scores").removeClass("two-no-scores");
    data = results['data'];
    the_score_container.find(".two_score_container.two_score_container_mobile .two-score-circle").data("score", data["current_score"]["mobile_score"]);
    the_score_container.find(".two_score_container.two_score_container_mobile .two_load_time").text(data["current_score"]["mobile_tti"] + 's');
    the_score_container.find(".two_score_container.two_score_container_desktop .two-score-circle").data("score", data["current_score"]["desktop_score"]);
    the_score_container.find(".two_score_container.two_score_contain  er_desktop .two_load_time").text(data["current_score"]["desktop_tti"] + 's');
    the_score_container.find('.two-new-scores .two-score-circle').each(function() {
      two_draw_score_circle(this);
    });
    the_score_container.find(".two_reanalyze_container .two-page-speed.two-optimizing").addClass("two-hidden");
    the_score_container.find(".two_reanalyze_button").text("Reanalyze");
  }
  the_score_container.find(".two_score_container_both .two_reanalyze_link").next(".two-page-speed.two-optimizing").addClass("two-hidden");
  jQuery(".two_admin_bar_menu_header.two_frontpage_not_optimized img, .two_admin_bar_menu_header.two_frontpage_optimized img").show();
  jQuery(".two_frontpage_optimizing span").addClass('two_hidden');
  the_score_container.find(".two_reanalyze_button").removeClass("two-button-disabled");
  the_score_container.find("#two_optimize_now_button").removeClass("two-button-disabled");
  jQuery('.two-pages-list-reanalyzing[data-post-id="' + post_id + '"]').addClass("two-hidden");
  jQuery('.two-optimized-see-more[data-post-id="' + post_id + '"]').removeClass("two-hidden");
}

/**
 * Run ajax action and Sign Up to dashboard via magic link
 *
 * @param that object
 */
function two_sign_up_dashboard_magic_link( that ) {
  if ( jQuery(that).hasClass("two-disable-link") ) {
    return false;
  }

  var email_input = jQuery(that).parent().parent().find(".two-sign-up-input");

  jQuery(".two-error-msg").remove();
  email_input.removeClass("two-input-error");
  jQuery(that).addClass('two-disable-link');
  jQuery(that).text('');
  jQuery(that).addClass('two-loading');

  var email = email_input.val();
  if (email === '') {
    window.location.href = two_speed.connection_link;
  }

  var EmailRegex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  if (!EmailRegex.test(email) && email !== '') {
    email_input.after('<p class="two-error-msg">' + two_speed.wrong_email + '</p>');
    email_input.addClass("two-input-error");
    jQuery(that).text(two_speed.sign_up);
    jQuery(that).removeClass('two-disable-link');
    jQuery(that).removeClass('two-loading');
    return;
  }
  jQuery.ajax( {
    type: "POST",
    url: two_speed.ajax_url,
    dataType: 'json',
    data: {
      action: "two_sign_up_dashboard_magic_link",
      email: email,
      nonce: two_speed.nonce,
    },
    success: function (result) {
      if ( result['status'] === 'success' ) {
        window.location.href = result['booster_connect_url'];
      }
      else {
        jQuery(that).text(two_speed.sign_up);
        jQuery(that).removeClass('two-disable-link');
        jQuery(that).removeClass('two-loading');
        email_input.after('<p class="two-error-msg">' + two_speed.something_wrong + '</p>');
        return;
      }
    },
    error: function (xhr) {
      jQuery(that).text(two_speed.sign_up);
      jQuery(that).removeClass('two-disable-link');
      jQuery(that).removeClass('two-loading');
      email_input.after('<p class="two-error-msg">' + two_speed.something_wrong + '</p>');
    }
  });
}