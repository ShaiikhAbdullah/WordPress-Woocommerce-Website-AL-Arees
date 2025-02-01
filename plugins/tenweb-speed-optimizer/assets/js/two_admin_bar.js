jQuery("document").ready(function () {
  var sheets = document.styleSheets;
  var two_styles_list = "";
  var exclude_css = ["dashicons-css", "admin-bar-css", "two_admin_bar_css-css"];
  var two_async_css = JSON.parse(two_admin_vars.two_async_css);
  var two_disable_css = JSON.parse(two_admin_vars.two_disable_css);
  var two_disable_page_css = JSON.parse(two_admin_vars.two_disable_css_page);
  var two_async_page_css = JSON.parse(two_admin_vars.two_async_css_page);
  var two_async_all = two_admin_vars.two_async_all;
  //var two_exclude_css = two_admin_vars.two_exclude_css;


  var two_disabled_option = "";
  if(two_async_all === "on"){
     two_disabled_option = "two_disabled_option";
  }

  jQuery('body').on('click', ".two_el_id", function () {
    var two_css_options_list = jQuery(this).closest("li").find(".two_css_options_list");
    if(two_css_options_list.hasClass("two_hidden")){
      two_css_options_list.removeClass("two_hidden");
      jQuery(this).addClass("two_down_arrow");
    }else {
      two_css_options_list.addClass("two_hidden");
      jQuery(this).removeClass("two_down_arrow");
    }
  });

  jQuery.each(sheets, function (index, value) {
    var uid = two_uid();
    var two_async_check = "";
    var two_async_page_check = "";
    var two_disabled_check = "";
    var two_disabled_page_check = "";
    var two_disabled_class = "class='two_disable'";
    var two_disabled_page_class = "class='two_disable_page'";
    var two_async_page_class = "class='two_async_page'";
    var two_async_class = "class='two_async'";
    var two_exclude_class = "class='two_exclude'";
    var el_flag = "";
    var el_id = "";
    var el_href = "";
    if(value.href !== null && value.href !=="" && typeof value.href !== "undefined") {
      el_href = value.href;
      el_id = stringToHash(value.href);
      jQuery(this).attr('id', el_id);
      el_flag = value.href;
      if (value.ownerNode.id !== null && value.ownerNode.id !== "" && typeof value.ownerNode.id!=="undefined") {
        el_flag = value.ownerNode.id;
      }
      if (two_async_css.includes(el_flag)) {
        two_async_check = "checked";
        two_async_class = "class='two_async two_checked'";
      }
      if (two_disable_css.includes(el_flag)) {
        two_disabled_check = "checked";
        two_disabled_class = "class='two_disable two_checked'";
      }
      if (two_disable_page_css.includes(el_flag)) {
        two_disabled_page_check = "checked";
        two_disabled_page_class = "class='two_disable_page two_checked'";
      }
      if (two_async_page_css.includes(el_flag)) {
        two_async_page_check = "checked";
        two_async_page_class = "class='two_async_page two_checked'";
      }
    }



    var two_style_options = "<div class='two_css_options_list two_hidden'>" +
        "<div class='two_option_group "+two_disabled_option+"'>" +
            "<label class='two_option_container'>Add to async for all pages" +
              "<input "+two_async_check+" name='"+uid+"'  data-two_action='two_async' "+two_async_class+" type='radio'>" +
              "<span class='wxao_checkmark'></span>" +
            "</label>" +
        "</div>"+

        "<div class='two_option_group "+two_disabled_option+"'>" +
            "<label class='two_option_container'>Add to async for this page" +
              "<input "+two_async_page_check+" name='"+uid+"' data-two_action='two_async_page' "+two_async_page_class+" type='radio'>" +
              "<span class='wxao_checkmark'></span>" +
            "</label>" +
        "</div>" +
        "<div class='two_option_group'>" +
            "<label class='two_option_container'>Disable for all pages" +
              "<input "+two_disabled_check+" name='"+uid+"' data-two_action='two_disable' "+two_disabled_class+" type='radio'>" +
              "<span class='wxao_checkmark'></span>" +
            "</label>"+
        "</div>" +
        "<div class='two_option_group'>" +
            "<label class='two_option_container'>Disable for this page" +
              "<input "+two_disabled_page_check+" name='"+uid+"' data-two_action='two_disable_page' "+two_disabled_page_class+" type='radio'>" +
              "<span class='wxao_checkmark'></span>" +
            "</label>" +
        "</div>";

    var flag = exclude_css.includes(el_flag);
    if(!flag && el_flag !== null && el_flag!== ""){
      two_styles_list+="<li class='two_options_section' title='"+el_flag+"' data-el_href='"+el_href+"' data-el_data_id='"+el_flag+"' data-el_id='"+el_id+"'><span class='two_el_id'><span>"+el_flag+"</span></span>"+two_style_options+"</li>";
    }
  });
  jQuery("#wp-admin-bar-two_options").append(
        '<div id="two_styles_list"><ul>'+two_styles_list+'</ul></div>'
  );
  jQuery(".two_checked").each(function () {
    if(jQuery(this).data("two_action") === "two_disable_page" || jQuery(this).data("two_action") === "two_disable"){
      var el_id = jQuery(this).closest("li").data("el_id");
      jQuery("#"+el_id).attr("rel" , "preload");
    }
  });


  jQuery('body').on('click', '.two_css_options_list input', function (){
    var two_action = jQuery(this).data("two_action");
    var el_id = jQuery(this).closest("li").data("el_id");
    var el_data_id = jQuery(this).closest("li").data("el_data_id");
    var el_href = jQuery(this).closest("li").data("el_href");
    var css_el = jQuery('link[href^="'+el_href+'"]');
    if(jQuery(this).hasClass("two_checked")){
      jQuery(this).removeClass("two_checked");
      jQuery(this).closest(".two_options_section").removeClass("two_check_section");
      jQuery(this).prop( "checked", false );
      //jQuery("#"+el_id).attr("rel" , "stylesheet");
      css_el.attr("rel" , "stylesheet");
      two_ajax_save(el_data_id , 0, two_action);
      return;
    }
    if(jQuery(this).is(":checked")) {
      jQuery(this).closest(".two_css_options_list").find("input").removeClass("two_checked");
      jQuery(this).addClass("two_checked");
      jQuery(this).closest(".two_options_section").addClass("two_check_section");

      if(two_action === "two_disable" || two_action === "two_disable_page"){
        css_el.attr("rel" , "preload");
      }else{
        css_el.attr("rel" , "stylesheet");
      }
      two_ajax_save(el_data_id , 1, two_action);
    }
  });
  jQuery(".two_checked").closest(".two_options_section").addClass("two_check_section");
});

function two_ajax_save(el_id, state,task) {
  var page_url = window.location.href;
  var two_async_data = "";
  var two_disable_data = "";
  var two_async_page = jQuery(".two_async_page");
  var two_disable_page = jQuery(".two_disable_page");
  two_async_page.each(function () {
    if(jQuery(this).is(":checked")) {
      two_async_data+=jQuery(this).closest("li").data("el_data_id")+",";
    }
  });
  two_disable_page.each(function () {
    if(jQuery(this).is(":checked")) {
      two_disable_data+=jQuery(this).closest("li").data("el_data_id")+",";
    }
  });

  jQuery.ajax({
    type: "POST",
    url: two_admin_vars.ajaxurl,
    dataType: 'json',
    data: {
      action: "two_css_options",
      task:task,
      el_id:el_id,
      state:state,
      two_async_page:two_async_data,
      two_disable_page:two_disable_data,
      page_url:page_url,
      nonce: two_admin_vars.ajaxnonce,
    }
  }).done(function (data) {

  });
}

function two_uid() {
  return 'two_' + Math.random().toString(36).substr(2, 9);
}
function stringToHash(string) {
  var hash = 0;
  if (string.length == 0) return hash;
  for (i = 0; i < string.length; i++) {
    char = string.charCodeAt(i);
    hash = ((hash << 5) - hash) + char;
    hash = hash & hash;
  }
  return "two"+hash;
}