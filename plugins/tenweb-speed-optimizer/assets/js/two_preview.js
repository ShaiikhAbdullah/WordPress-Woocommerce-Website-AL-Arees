jQuery("document").ready(function () {
    jQuery("style").remove();
    jQuery("link:not(#two_preview_css-css):not(#two_google-fonts-css)").remove();
    jQuery("body").html('<div id="two_preview_container"></div>')

    let two_local_flow_id = localStorage.getItem("two_flow_id");
    let two_flow_id = two_preview_vars.flow_id;

    if(two_local_flow_id !== two_flow_id){
        two_update_flow_status("1");
        localStorage.removeItem("two_preview_mode");
        localStorage.removeItem("two_mode_flow_status");
        localStorage.setItem("two_flow_id", two_flow_id);
    }
    let two_preview_mode = localStorage.getItem("two_preview_mode");
    let two_mode_flow_status = localStorage.getItem("two_mode_flow_status");

    let two_modes = JSON.parse(two_preview_vars.two_modes);
    let two_incompatible_plugins = false;
    let two_mandatory_incompatible_plugins = false;
    const mandatory_plugins = ['wp-optimize/wp-optimize.php', 'wp-rocket/wp-rocket.php', 'nitropack/main.php'];
    if(two_preview_vars.incompatible_plugins){
        two_incompatible_plugins = JSON.parse(two_preview_vars.incompatible_plugins);
    }
    if (two_incompatible_plugins) {
        two_mandatory_incompatible_plugins = !!Object.keys(two_incompatible_plugins).find((plugin) => mandatory_plugins.includes( plugin ))
    }
    let current_mode_key = "extreme";
    let current_mode = two_modes[current_mode_key];
    let current_mode_level = 1;
    let current_mode_next = current_mode["next"];

    if (two_preview_mode && two_modes[two_preview_mode]["next"]) {
        current_mode = two_modes[two_preview_mode];
        current_mode_key = current_mode["mode"];
        current_mode_level = parseInt(current_mode["level"]);
        current_mode_next = current_mode["next"];
    }

    let two_preview_contact_us_html = get_two_preview_contact_us_html();
    let two_incompatible_plugins_html = get_two_incompatible_plugins_html(two_mandatory_incompatible_plugins);
    let two_preview_html = get_two_preview_html(current_mode, two_modes);

    if(current_mode_next === "0"){
        jQuery("#two_preview_container").html(two_preview_contact_us_html);
    }else if(two_mode_flow_status && two_incompatible_plugins){
        jQuery("#two_preview_container").html(two_incompatible_plugins_html);
    }else{
        jQuery("#two_preview_container").html(two_preview_html);
    }
    jQuery("body").on("click", ".two_change_mode", function () {
        current_mode = two_modes[current_mode_next];
        current_mode_key = current_mode["mode"];
        current_mode_level = parseInt(current_mode["level"]);
        current_mode_next = current_mode["next"];

        if(current_mode_next === "0"){
            // contact_us
            // set mode no_optimize
            jQuery("#two_preview_container").html(two_preview_contact_us_html);
            two_update_flow_status("3");
            two_flow_set_mode("extreme" , "1");
        }else{
            let two_preview_html = get_two_preview_html(current_mode, two_modes);
            jQuery("#two_preview_container").html(two_preview_html);
        }
        jQuery("#two_preview_iframe").attr("src", current_mode.preview_url);
        localStorage.setItem("two_preview_mode", current_mode_key);
    });
    jQuery("body").on("click", ".two_save_mode", function () {
        //finish flow set global mode
        //check incompatible plugins
        let global_set_mode = jQuery(this).data("mode");
        localStorage.setItem("two_mode_flow_status", "1");
        localStorage.setItem("two_preview_mode", current_mode_key);
        two_update_flow_status("2");
        if(two_incompatible_plugins) {
            two_flow_set_mode(global_set_mode);
            jQuery("#two_preview_container").html(two_incompatible_plugins_html);
        }else{
            jQuery("#two_looks_good_form").submit();
        }
    });
    jQuery("body").on("click", ".two_contact_us_button", function (e) {
        e.preventDefault();
        two_update_flow_status("4");
        window.location.replace(two_preview_vars.contact_us_url);
    });

    jQuery("body").append("<iframe id='two_preview_iframe' src='"+current_mode.preview_url+"'></iframe>");

    jQuery('.two_incompatible_plugin label').click(function() {
        if( jQuery('.two_checkbox:checkbox:checked').length === 0 ) {
            jQuery('.two_disable_incompatible_plugins').addClass('deactivated').prop('disabled', true);
        } else {
            jQuery('.two_disable_incompatible_plugins').removeClass('deactivated').prop('disabled', false);
        }
    });
});

/*jQuery(document).on('click','.two_preview_close',function() {
    jQuery('.two_preview_tools').toggleClass('two_preview_tools_closed');
});*/


function get_two_incompatible_plugins_html(two_mandatory_incompatible_plugins) {
    if(two_preview_vars.incompatible_plugins){
        let two_incompatible_plugins = JSON.parse(two_preview_vars.incompatible_plugins);
        let incompatible_plugins_html = "";
        for (let two_i in two_incompatible_plugins) {
            if (two_mandatory_incompatible_plugins) {
                incompatible_plugins_html += `<div class='two_incompatible_plugin with-restricted'><input class="two_checkbox" name="incompatible_plugins[]" checked type="checkbox" value="` + two_i + `"><span class="restricted-mark"></span>` + two_incompatible_plugins[two_i] + `</div>`;
            }
            else {
                incompatible_plugins_html += `<div class='two_incompatible_plugin'><label>
                                                <input class="two_checkbox" name="incompatible_plugins[]" checked type="checkbox" value="` + two_i + `">
                                                ` + two_incompatible_plugins[two_i] + `<span class="checkmark"></span></label>
                                         </div>`;
            }
        }
        let return_html = `<div class='two_not_available'>` + two_preview_vars.two_company_name + ` Booster onboarding flow <br/>is available only on desktop</div>
        <div class='two_preview_tools'>
           <div class="two_incompatible_plugins_container">`;
        if (two_mandatory_incompatible_plugins) {
            return_html += `<p class="two_preview_tools_title">` + two_preview_vars.two_company_name + ` Booster will not work with these plugins</p>
                            <p class="two_preview_tools_desc last-step">Proceeding will temporarly deactivate these plugins.</p>`
        }
        else {
            return_html += `<p class="two_preview_tools_title">Some plugins are conflicting with ` + two_preview_vars.two_company_name + ` Booster</p>
                            <p class="two_preview_tools_desc last-step">We recommend deactivating these plugins. By clicking ‘Next’ the selected plugins will be deactivated.</p>`
        }

        return_html += `<div class="two_preview_tools_info two_preview_tools_content incompatible_plugins">
                   <form action="` + two_preview_vars.ajaxurl + `" method="post">
                    <div class="two_incompatible_plugins">
                            ` + incompatible_plugins_html + `
                    </div>
                    <input type="hidden" name="nonce" value="`+two_preview_vars.ajaxnonce+`">
                    <input type="hidden" name="action" value="two_flow_incompatible_plugins">
                   <div class="two_preview_tools_content_buttons">
                       <button class="two_disable_incompatible_plugins two_preview_button" name="two_disable_incompatible_plugins" type="submit">NEXT</button>
                   </div>
                   </form>
               </div>
           </div>
       </div>`;
        return return_html;
    }
    return "";

}

function get_two_preview_html(current_mode, two_modes) {
    let two_next_mode_key = current_mode["next"];
    if(two_next_mode_key === "0"){
        return "";
    }

    let next_mode = two_modes[two_next_mode_key];
    let current_mode_level = parseInt(current_mode["level"]);
    let current_mode_key = current_mode["mode"];

    return_html = `
       <div class='two_not_available'>` + two_preview_vars.two_company_name + ` Booster onboarding flow <br/>is available only on desktop</div>
       <div class='two_preview_tools'>
          <div class="two_preview_tools-wrap">
            <div class='two_preview_tools-first-block'>
               <p class="two_preview_tools_title with_icon">` + two_preview_vars.two_company_name + ` Booster</p>
               <p class="two_preview_tools_desc first-step">This is the preview of your optimized homepage.</p>
               <p class="two_preview_tools_notice"><span>Your live website is unaffected.</span></p>
            </div>`;

    if (current_mode_level === 1) {
        return_html += `
           <div class="two_preview_tools_content">
               <p class="two_preview_tools_desc two_bold">Is this preview identical to your live homepage?</p>`;
    } else {

        return_html += `
           <div class="two_preview_tools_content">
           <p class="two_preview_count">Preview ` + current_mode_level + `</p>
           <p class="two_preview_tools_desc">Is this preview identical to your live homepage?</p>`;
    }
    return_html += `
       <div class="two_preview_tools_buttons">
                <form style="display: none !important;" id="two_looks_good_form" action="` + two_preview_vars.ajaxurl + `" method="post">
                    <input type="hidden" name="action" value="two_flow_set_mode">
                    <input type="hidden" name="mode" value="`+current_mode_key+`">
                    <input type="hidden" name="test_mode" value="0">
                    <input type="hidden" name="redirect" value="1">
                    <input type="hidden" name="nonce" value="`+two_preview_vars.ajaxnonce+`">
                </form>
                <p class="two_change_mode" data-level="` + next_mode['level'] + `" data-mode="` + two_next_mode_key + `" data-url="` + next_mode['preview_url'] + `">Something is off</p>
               <p class="two_save_mode" data-mode="`+current_mode_key+`">Looks good</p>
           </div>
       </div>
     </div>
   </div>`

    return return_html;
}

function get_two_preview_contact_us_html() {
    let return_html = `
          <div class='two_not_available'>` + two_preview_vars.two_company_name + ` Booster onboarding flow <br/>is available only on desktop</div>
          <div class='two_preview_tools'>
              <div class="two_preview_tools_content_centered">
                  <p class="two_preview_tools_title">We know how to help you!</p>
                  <p class="two_preview_tools_desc last-step">We’re sorry you’re still having issues. A unique website like yours requires a unique solution.</p>
                  <div class="two_preview_tools_notice last-step">
                    <div>
                      <span class="two_preview_chat"></span>
                      <div class="two_contact_us-desc">Please drop us a message and we’ll try to fix it on our end.</div>
                    </div>
                    <a class="two_contact_us_button two_preview_button" href="\` + two_preview_vars.contact_us_url + \`">CONTACT US</a>
                  </div>
              </div>
          </div>`
    return return_html;
}

function two_flow_set_mode(mode , test_mode="0", redirect_url = false){
    jQuery.ajax({
        type: "POST",
        url: two_preview_vars.ajaxurl,
        dataType: 'json',
        data: {
            action: "two_flow_set_mode",
            mode: mode,
            test_mode: test_mode,
            nonce: two_preview_vars.ajaxnonce,
        }
    }).done(function (data) {
        if(redirect_url){
            window.location.href = redirect_url;
        }
    });
}

function two_update_flow_status(status){
    jQuery.ajax({
        type: "POST",
        url: two_preview_vars.ajaxurl,
        dataType: 'json',
        data: {
            action: "two_update_flow_status",
            status:status,
            nonce: two_preview_vars.ajaxnonce,
        }
    }).done(function (data) {

    });
}
