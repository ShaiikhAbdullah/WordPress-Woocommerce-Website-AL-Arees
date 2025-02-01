var two_table_critical;
jQuery("document").ready(function () {
    jQuery('#two_delay_js_exclusions').multiSelect();
    jQuery('#two_async_css,#two_exclude_lazyload,#two_exclude_images_for_optimize,#two_exclude_delay_js,#two_disabled_delay_all_js_pages, ' +
        '#two_exclude_css,#two_exclude_js,#two_disable_css,#two_delayed_js_execution_list,#two_load_excluded_js_normally,' +
        '#two_fonts_to_preload, #two_disabled_speed_optimizer_pages, #two_fonts_to_preconnect, #two_non_optimizable_speed_optimizer_pages').tagsInput({
        width: 'auto',
        'defaultText': 'add an item',
        interactive: true,
        autocomplete: {
            delay: 100,
            disabled: false,
            minInputWidth: '200px'
        },
    });
    var two_table = jQuery('#two_page_css_table').DataTable({"paging": false,});


    jQuery("#enable_lazyload, #enable_bg_lazyload, #enable_video_lazyload, #enable_iframe_lazyload").change(function () {
        if (jQuery("#enable_iframe_lazyload").is(":checked")) {
            jQuery(".two_elemrntor_video_iframe").css({
                'display': 'block',
            });
            jQuery(".two_delay_iframe_lazyload").css({
                'display': 'block',
            });
        } else {
            jQuery(".two_elemrntor_video_iframe").css({
                'display': 'none',
            });
            jQuery(".two_delay_iframe_lazyload").css({
                'display': 'none',
            });
        }

        if (this.checked) {
            jQuery(".two_exclude_lazyload").css({
                'display': 'block',
            });
        } else {
            if (jQuery("#enable_lazyload").is(":checked") || jQuery("#enable_bg_lazyload").is(":checked") || jQuery("#enable_iframe_lazyload").is(":checked") || jQuery("#enable_video_lazyload").is(":checked")) {
                return;
            }
            jQuery(".two_exclude_lazyload").css({
                'display': 'none',
            });
        }
    });

    jQuery("#do_not_optimize_images").change(function () {
        if (this.checked) {
            jQuery(".two_exclude_images_for_optimize").css({
                'display': 'none',
            });

        } else {
            if (jQuery("#exclude_images_for_optimize").is(":checked")) {
                return;
            }
            jQuery(".two_exclude_images_for_optimize").css({
                'display': 'block',
            });
        }
    });


    jQuery("#two_async_all").change(function () {
        if (this.checked) {
            jQuery(".two_async_css").css({
                'display': 'none',
            });
        } else {
            jQuery(".two_async_css").css({
                'display': 'block',
            });
        }
    });
    jQuery("#two_critical").change(function () {
        if (this.checked) {
            jQuery("#two_critical_options").css({
                'display': 'block',
            });
        } else {
            jQuery("#two_critical_options").css({
                'display': 'none',
            });
        }
    });

    jQuery("#two_delay_js_execution").change(function () {
        if (this.checked) {
            jQuery(".two_delayed_js_load_libs_first, .two_delayed_js_execution_list").css({
                'display': 'block',
            });
        } else {
            jQuery(".two_delayed_js_load_libs_first, .two_delayed_js_execution_list").css({
                'display': 'none',
            });
        }
    });

    jQuery("#lazy_load_type").change(function () {
        if (this.value === "browser") {
            jQuery(".enable_youtube_vimeo_iframe_lazyload").addClass("two_hidden");
            jQuery(".two_bg_lazyload_cont, .two_img_in_viewport_lazyload_cont").css({
                "display": "none"
            });
        } else {
            jQuery(".enable_youtube_vimeo_iframe_lazyload").removeClass("two_hidden");
            jQuery(".two_bg_lazyload_cont, .two_img_in_viewport_lazyload_cont").css({
                "display": "block"
            });
        }
    });

    jQuery("#enable_lazyload").change(function() {
        if (this.checked) {
            jQuery(".two_add_noscript").css({
                'display': 'block',
            });
        } else {
            jQuery(".two_add_noscript").css({
                'display': 'none',
            });
        }
    });

    jQuery("#two_serve_gzip").change(function() {
        if (this.checked) {
            jQuery(".two_empty_encoding_serve_gzip").css({
                'display': 'block',
            });
        } else {
            jQuery(".two_empty_encoding_serve_gzip").css({
                'display': 'none',
            });
        }
    });

    jQuery(".two_clear_cache").click(function (e) {
        clear_messages();
        e.preventDefault();
        jQuery.ajax({
            type: "POST",
            url: two_admin_vars.ajaxurl,
            dataType: 'json',
            data: {
                action: "two_settings",
                task: "clear_cache",
                nonce: two_admin_vars.ajaxnonce,
            }
        }).done(function (data) {
            if (data.success) {
                jQuery(".two_success_message").css({
                    'display': 'block'
                });
            } else {
                jQuery(".two_error_message").css({
                    'display': 'block'
                });
            }
        });
    });
    jQuery(".two_finish_flow").click(function (e) {
        jQuery.ajax({
            type: "POST",
            url: two_admin_vars.ajaxurl,
            dataType: 'json',
            data: {
                action: "two_finish_flow",
                nonce: two_admin_vars.ajaxnonce,
            }
        }).done(function (data) {
            jQuery(".two_finish_flow").remove();
        });
    });

    jQuery(".two_webp_action").click(function (e) {
        clear_messages();
        e.preventDefault();
        let task = jQuery(this).attr('data-action');
        let url_list = jQuery('#two_create_webp_list').val();
        jQuery.ajax({
            type: "POST",
            url: two_admin_vars.ajaxurl,
            dataType: 'json',
            data: {
                action: "two_settings",
                task: task,
                url_list: url_list,
                nonce: two_admin_vars.ajaxnonce,
            }
        }).done(function (data) {
            if ( 'success' == data.status ) {
                jQuery(".two_webp_success_message").css({
                    'display': 'block'
                });
                jQuery('#two_create_webp_list').val( '' );
            } else {
                jQuery(".two_webp_error_message").css({
                    'display': 'block'
                });
            }
        });
    });

    jQuery(".two_regenerate_critical").click(function (e) {
        clear_messages();
        e.preventDefault();
        jQuery(".two_generate_critical").addClass("two_disabled");
        jQuery(".two_critical_pages .spinner").addClass("is-active");
        jQuery.ajax({
            type: "POST",
            url: two_admin_vars.ajaxurl,
            dataType: 'json',
            data: {
                action: "two_settings",
                task: "regenerate_critical",
                nonce: two_admin_vars.ajaxnonce,
            }
        }).done(function (data) {
            if (data.success) {
                jQuery(".two_success_message").css({
                    'display': 'block'
                });
            } else {
                jQuery(".two_error_message").css({
                    'display': 'block'
                });
            }
        });
    });
    jQuery("#enable_lazyload").change(function () {
        var checked = 0;
        if (jQuery(this).is(':checked')) {
            checked = 1;
        }

    });


    jQuery("#enable_js_aggregate").change(function () {
        if (this.checked) {
            jQuery(".two_include_inline_js, .two_use_extended_exception_list_js").css({
                'display': 'block'
            });
        } else {
            jQuery(".two_include_inline_js, .two_use_extended_exception_list_js").css({
                'display': 'none'
            });

        }
    });

    jQuery("#two_include_inline_js").change(function () {
        if (this.checked) {
            jQuery(".two_use_extended_exception_list_js").css({
                'display': 'block'
            });
        } else {
            jQuery(".two_use_extended_exception_list_js").css({
                'display': 'none'
            });
        }
    });

    jQuery("#enable_css_aggregate").change(function () {
        if (this.checked) {
            jQuery(".two_include_inline_css").css({
                'display': 'block'
            });
        } else {
            jQuery(".two_include_inline_css").css({
                'display': 'none'
            });

        }
    });
    jQuery("#two_page_cache").change(function () {
        if (this.checked) {
            jQuery("#two_page_cache_life_time, #two_page_cache_user").parent().css({
                'display': 'block'
            });
        } else {
            jQuery("#two_page_cache_life_time, #two_page_cache_user").parent().css({
                'display': 'none'
            });

        }
    });
    jQuery("#two_delay_all_js_execution").change(function () {
        if (this.checked) {
            jQuery(".two_js_options").css({
                'display': 'none'
            });
            jQuery(".two_exclude_delay_js,.two_delay_custom_js_new,.two_disabled_delay_all_js_pages").css({
                'display': 'block'
            });
            jQuery(".two_timeout_js_load, .two_load_excluded_js_via_worker,.two_exclude_rev,.two_exclude_slider_by_10web,.two_exclude_elementor_scripts,.two_exclude_photo_gallery_by_10web,.two_exclude_amp_plugin_cdn").css({
                'display': 'block'
            });
            jQuery(".two_load_excluded_js_normally").css({
                'display': 'block'
            });
            jQuery(".two_plugins_to_exclude").css({
                'display': 'block'
            });
        } else {
            jQuery(".two_plugins_to_exclude").css({
                'display': 'none'
            });
            jQuery(".two_js_options").css({
                'display': 'block'
            });
            jQuery(".two_exclude_delay_js,.two_delay_custom_js_new,.two_disabled_delay_all_js_pages").css({
                'display': 'none'
            });
            jQuery(".two_timeout_js_load, .two_load_excluded_js_via_worker,.two_exclude_rev,.two_exclude_slider_by_10web,.two_exclude_elementor_scripts,.two_exclude_photo_gallery_by_10web,.two_exclude_amp_plugin_cdn").css({
                'display': 'none'
            });
            jQuery(".two_load_excluded_js_normally").css({
                'display': 'none'
            });
        }
    });

    jQuery("#two_load_excluded_js_via_worker").change(function () {
        if (this.checked) {
            jQuery(".two_load_excluded_js_normally").css({
                'display': 'block'
            });
        } else {
            jQuery(".two_load_excluded_js_normally").css({
                'display': 'none'
            });
        }
    });

    jQuery(".two_save_settings").click(function (e) {
        clear_messages();
        e.preventDefault();

        var async_css_list_page = {};
        var disabled_css_list_page = {};

        jQuery("#two_page_css_table tbody tr").each(function () {
            var two_load_type = jQuery(this).find(".two_load_type").text();
            var two_url = jQuery(this).find(".two_url").text();
            var two_css_name = jQuery(this).find(".two_css_name").text();
            if (typeof two_url !== "undefined" && typeof two_css_name !== "undefined") {
                if (two_url.length !== 0 && two_css_name.length !== 0) {
                    if (two_load_type === "Async") {
                        if (typeof async_css_list_page[two_url] !== "undefined") {
                            async_css_list_page[two_url] = async_css_list_page[two_url] + "," + two_css_name;
                        } else {
                            async_css_list_page[two_url] = two_css_name;
                        }

                    } else if (two_load_type === "Disabled") {
                        if (typeof disabled_css_list_page[two_url] !== "undefined") {
                            disabled_css_list_page[two_url] = disabled_css_list_page[two_url] + "," + two_css_name;
                        } else {
                            disabled_css_list_page[two_url] = two_css_name;
                        }
                    }
                }
            }
        });
        var two_critical_url_args = jQuery("#two_critical_url_args").val();
        var two_critical_sizes = two_get_critical_sizes();
        var two_critical_pages = two_two_critical_pages('front_page');
        var two_critical_status = jQuery("#two_critical").is(':checked');
        var two_critical_remove = jQuery("#two_critical_remove").is(':checked');
        var two_critical_font_status = jQuery("#two_critical_font").is(':checked');
        var two_delay_js_exclusions = jQuery('#two_delay_js_exclusions').val();
        var two_events_after_load = jQuery('#two_events_after_load').val();
        var two_generate_ccss_on_load = jQuery('#two_generate_ccss_on_load').val();
        var two_font_actions = jQuery('#two_font_actions').val();
        var ajax_data = {
            'action': 'two_settings',
            'task': 'settings',
            'two_async_page': async_css_list_page,
            'two_disable_page': disabled_css_list_page,
            'two_critical_pages': two_critical_pages,
            'two_critical_url_args': two_critical_url_args,
            'two_critical_sizes': two_critical_sizes,
            'two_critical_status': two_critical_status,
            'two_critical_remove': two_critical_remove,
            'two_critical_font_status': two_critical_font_status,
            'two_font_actions': two_font_actions,
            'nonce': two_admin_vars.ajaxnonce
        };
        var two_form_data = jQuery('.two_settings_form').serializeArray();
        jQuery.each(two_form_data, function (key, val) {
            if (val.name === "two_delay_js_exclusions" && two_delay_js_exclusions !== null) {
                ajax_data["two_delay_js_exclusions"] = two_delay_js_exclusions;
            } else if (val.name === "two_events_after_load" && two_events_after_load !== null) {
                ajax_data["two_events_after_load"] = two_events_after_load;
            } else if (val.name === "two_generate_ccss_on_load" && two_generate_ccss_on_load !== null) {
                ajax_data["two_generate_ccss_on_load"] = two_generate_ccss_on_load;
            } else {
                ajax_data[val.name] = val.value;
            }
        });

        jQuery('.two_save_settings').attr('disabled', 1);
        jQuery('.wd-left-side .spinner').addClass('is-active');
        jQuery.ajax({
            type: "POST",
            dataType: 'json',
            url: two_admin_vars.ajaxurl,
            data: ajax_data,
        }).done(function (data) {
            if (data.success) {
                jQuery('.wd-left-side .spinner').removeClass('is-active');
                jQuery('.two_save_settings').removeAttr('disabled');
                jQuery(".two_success_message p").text( data.message );
                if ( true == data.webp_delivery_status ) {
                    jQuery("#two_webp_delivery_working").removeClass('two_hidden');
                    jQuery(".two_enable_picture_webp_delivery").addClass('two_hidden');
                }
                else {
                    jQuery("#two_webp_delivery_working").addClass('two_hidden');
                    jQuery(".two_enable_picture_webp_delivery").removeClass('two_hidden');
                }
                if ( 'nginx_webp_delivery' == data.code ) {
                    jQuery( '#two_enable_nginx_webp_delivery' ).prop( 'checked', !jQuery( '#two_enable_nginx_webp_delivery' ).prop( 'checked' ) );
                    jQuery(".two_success_message").removeClass('notice-success').addClass('notice-warning');
                }
                jQuery(".two_success_message").css({
                    'display': 'block'
                });
            } else {
                jQuery(".two_error_message").css({
                    'display': 'block'
                });
            }
        });
    });

    jQuery('.two_tab_two_logs .button').on("click", function (e) {
        let btn = this;

        let log_type = btn.getAttribute("data-log-type");
        if(!log_type){
            return;
        }

        let btn_text = btn.textContent;
        btn.textContent = "Deleting";

        jQuery.ajax({
            type: "POST",
            url: two_admin_vars.ajaxurl,
            dataType: 'json',
            data: {
                action: "two_settings",
                task: "delete_logs",
                log_type: log_type,
                nonce: two_admin_vars.ajaxnonce,
            }
        }).done(function (data) {
            btn.textContent = btn_text;

            if (data.success) {
                jQuery("table[data-log-type='"+log_type+"'] tbody th").each(function(i, el){
                    el.remove();
                });
            } else {
                alert("Something went wrong");
            }
        });
    });

    jQuery("body").on("click", ".two_delete_element", function () {
        two_table
            .row(jQuery(this).parents('tr'))
            .remove()
            .draw();
    });


    /*edit and add disabled or async table */


    jQuery("body").on("click", ".two_edit_element", function () {
        var task = jQuery(this).data("task");
        var delete_task = jQuery(this).data("delete-task");
        var name = jQuery(this).data("name");
        var url = jQuery(this).data("url");
        if (task === "edit") {
            jQuery(this).data('task', "save");
            jQuery(this).removeClass("dashicons-edit");

            jQuery(this).addClass("two_save_element");
            jQuery(this).addClass("dashicons-saved");
            var $row = jQuery(this).closest("tr").off("mousedown");
            var $tds = $row.find("td").not(':last');
            jQuery.each($tds, function (i, el) {
                var txt = jQuery(this).text();
                if (jQuery(this).hasClass("two_load_type")) {
                    var options_list = "<option value='Disabled'>Disabled</option><option value='Async' selected>Async</option>";
                    if (txt === "Disabled") {
                        options_list = "<option value='Disabled' selected>Disabled</option><option value='Async'>Async</option>";
                    }
                    jQuery(this).html("").append("<select>" + options_list + "</select>");
                } else {
                    jQuery(this).html("").append("<input type='text' value=\"" + txt + "\">");
                }
            });
        } else {
            jQuery(this).addClass("dashicons-edit");
            jQuery(this).removeClass("dashicons-saved");
            jQuery(this).data('task', "edit");
            var $row = jQuery(this).closest("tr");
            var $tds = $row.find("td").not(':last');

            jQuery.each($tds, function (i, el) {
                if (jQuery(this).hasClass("two_load_type")) {
                    var txt = jQuery(this).find("select").val();
                } else {
                    var txt = jQuery(this).find("input").val();
                }
                jQuery(this).html(txt);
            });
        }
    });

    jQuery('<div><button id="two_add_new_element">Add New Row</button></div>').insertAfter('#two_page_css_table');
    jQuery('#two_add_new_element').click(function () {
        var two_new_row_html = get_two_new_row_html();
        two_table.row.add(jQuery(two_new_row_html)).draw();
    });
    jQuery("body").on("click", ".two_duplicate_element", function () {
        //two_css_name, two_url , two_load_type
        var row_tr = jQuery(this).closest("tr");
        var two_css_name = row_tr.find(".two_css_name").html();
        var two_url = row_tr.find(".two_url").html();
        var two_load_type = row_tr.find(".two_load_type").html();
        var two_duplicate_html = get_two_duplicate_html(two_css_name, two_url, two_load_type);
        two_table.row.add(jQuery(two_duplicate_html)).draw();
    });
    /*-----------------------------------------------------*/


    jQuery(".two_tab_menus .two_tab_menu").click(function () {
        jQuery(".two_tab_menus .two_tab_menu").removeClass("active");
        jQuery(".two_settings_tab").removeClass("active");
        var data_tab = jQuery(this).data("tab");
        jQuery(".two_tab_" + data_tab).addClass("active");
        jQuery(this).addClass("active");
    });


    /*critical css */
    //jQuery('.two_critical_sizes_select').multiSelect();
    two_table_critical = jQuery('#two_critical_pages').DataTable({"paging": false,});
    var two_table_sizes = jQuery('#two_critical_sizes').DataTable({"paging": false,});
    jQuery("#two_page_for_critical").select2({
        templateResult: formatState,
        templateSelection: formatState,
        ajax: {
            url: ajaxurl,
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term,
                    action: 'two_get_posts_for_critical',
                    nonce: two_admin_vars.ajaxnonce,
                };
            },
            processResults: function( data ) {
                var options = [];
                if ( data ) {
                    jQuery.each( data, function( index, text ) {
                        options.push( { id: text[0], text: text[1], url: text[2], flag_url: text[3]  } );
                    });
                }
                return {
                    results: options
                };
            },
            cache: true
        }
    });
    jQuery("#two_events_after_load").select2({
        templateResult: formatState,
        templateSelection: formatState,
        width: 300,
        placeholder: jQuery("#two_events_after_load").attr('placeholder'),
    });
    jQuery("#two_generate_ccss_on_load").select2({
        templateResult: formatState,
        templateSelection: formatState,
        width: 300,
        placeholder: jQuery("#two_generate_ccss_on_load").attr('placeholder'),
    });
    function formatState(opt){
        if (!opt.id || !opt.flag_url) {
            return opt.text;
        }

        return jQuery('<span>' + '<img src="' + opt.flag_url + '" class="two_flag"/>' + opt.text + '</span>');
    }

    jQuery(".two_add_critical_css_row").click(function () {
        var data = jQuery("#two_page_for_critical").select2('data')[0];
        if (data) {
            var url = data.url;
            var title = data.text;
            var id = data.id;
            var flag_url = data.flag_url;
            // Avoid dublicates.
            if (two_table_critical.rows('[data-page_id=' + id + ']')[0].length == 0) {
                jQuery('#two_critical_pages').addClass( 'loading' );
                two_table_critical.row.add( jQuery( get_two_table_critical_html( url, id, title, flag_url) ) ).draw();
                //let two_critical_sizes_select = jQuery('tr[data-page_id=' + id + '] .two_critical_sizes_select option').prop('selected', true);

                var two_critical_pages = two_two_critical_pages(id);
                var ajax_data = {
                    'action': 'two_critical',
                    'data': {
                        page_id: id,
                        task: "insert/update",
                        'two_critical_pages': two_critical_pages,
                    },
                    'nonce': two_admin_vars.ajaxnonce,
                };
                jQuery.ajax({
                    type: "POST",
                    dataType: 'json',
                    url: two_admin_vars.ajaxurl,
                    data: ajax_data,
                }).done(function (data) {
                    jQuery('#two_critical_pages').removeClass( 'loading' );
                });
            }
        }
    });

    jQuery("body").on("change", "td.critical_page_sizes>select, td.critical_page_load_actions>select, td.critical_page_wait_until>select, td.use_uncritical>input",function () {
        var id = jQuery(this).closest('tr').attr('data-page_id');
        if ( id ) {
            jQuery('#two_critical_pages').addClass( 'loading' );
            var two_critical_pages = two_two_critical_pages(id);
            var ajax_data = {
                'action': 'two_critical',
                'data': {
                    page_id: id,
                    task: "insert/update",
                    'two_critical_pages': two_critical_pages,
                },
                'nonce': two_admin_vars.ajaxnonce,
            };
            jQuery.ajax({
                type: "POST",
                dataType: 'json',
                url: two_admin_vars.ajaxurl,
                data: ajax_data,
            }).done(function (data) {
                jQuery('#two_critical_pages').removeClass( 'loading' );
            });
        }
    });

    jQuery("body").on("change", "td.critical_page_sizes_default>select, td.critical_page_load_actions_default>select, td.critical_page_wait_until_default>select, td.use_uncritical_default>input",function () {
        jQuery('#two_critical_pages').addClass( 'loading' );
        let two_critical_default_settings = get_two_critical_default_settings();
        var ajax_data = {
            'action': 'two_critical',
            'data': {
                task: "update_defaults",
                "default_settings":two_critical_default_settings
            },
            'nonce': two_admin_vars.ajaxnonce,
        };
        jQuery.ajax({
            type: "POST",
            dataType: 'json',
            url: two_admin_vars.ajaxurl,
            data: ajax_data,
        }).done(function (data) {
            jQuery('#two_critical_pages').removeClass( 'loading' );
        });
    });



    jQuery('<div><button id="two_add_new_critical_size">Add New Size</button></div>').insertAfter('#two_critical_sizes');
    jQuery("#two_add_new_critical_size").click(function () {
        two_table_sizes.row.add(jQuery(get_two_table_sizes_html())).draw();
    });
    jQuery("body").on("click", ".two_add_critical_size", function () {
        var $row = jQuery(this).closest("tr");
        var uid =  $row.data("uid");
        var $tds = $row.find("td").not(':last');
        var new_size_width = 0;
        var new_size_height = 0;
        var critical_size_media_input = jQuery(this).find(".critical_size_media_input").val();
        jQuery.each($tds, function (i, el) {
            var critical_page_size_input = jQuery(this).find(".critical_page_size_input");
            var txt = critical_page_size_input.val();
            if (critical_page_size_input.hasClass("critical_page_width_input")) {
                new_size_width = txt;
            }
            if (critical_page_size_input.hasClass("critical_page_height_input")) {
                new_size_height = txt;
            }
            jQuery(this).html(txt);
        });

        var new_option = new Option(new_size_width + "/" + new_size_height);
        jQuery(new_option).data("width", new_size_width);
        jQuery(new_option).data("height", new_size_height);
        jQuery(new_option).data("uid", uid);
        jQuery(new_option).data("media", critical_size_media_input);
        jQuery(new_option).html(new_size_width + "/" + new_size_height);
        jQuery(".two_critical_sizes_select").append(new_option);

        jQuery(this).remove();
    });
    jQuery("body").on("click", ".two_delete_critical_size", function () {
        two_table_sizes
            .row(jQuery(this).parents('tr'))
            .remove()
            .draw();
    });
    jQuery("body").on("click", ".two_delete_critical_page", function () {
        two_table_critical
            .row(jQuery(this).parents('tr'))
            .remove()
            .draw();

        var page_id = jQuery(this).data("page_id");
        var ajax_data = {
            'action': 'two_critical',
            'data': {
                page_id: page_id,
                task: "delete",
            },
            'nonce': two_admin_vars.ajaxnonce,
        };
        jQuery.ajax({
            type: "POST",
            dataType: 'json',
            url: two_admin_vars.ajaxurl,
            data: ajax_data,
        }).done(function (data) {

        });
    });

    jQuery("body").on("click", ".two_clear_cloudflare_cache", function () {
        let page_url = jQuery(this).data("page_url");
        let ajax_data = {
            'action': 'two_clear_cloudflare_cache',
             page_url: page_url,
            'nonce': two_admin_vars.ajaxnonce,
        };
        let el = jQuery(this).parents('tr');
        let spinner = el.find(".spinner");
        spinner.addClass("is-active");
        jQuery.ajax({
            type: "POST",
            dataType: 'json',
            url: two_admin_vars.ajaxurl,
            data: ajax_data,
        }).done(function (data) {
            spinner.removeClass("is-active");
        });
    });


    jQuery("body").on("click", ".two_generate_critical", function () {
        jQuery(".two_generate_critical").addClass("two_disabled_timeout");
        let two_generate_critical_button = jQuery(this);
        two_generate_critical(two_generate_critical_button);
    });


    let two_critical_generation_flag = true;
    function two_generate_critical(_this){
        two_critical_generation_flag = false;
        two_check_critical_restart();
        _this.addClass("two_disabled");
        var el = _this.parents('tr');
        var spinner = el.find(".spinner");
        el.find(".two_critical_page_status").val("in_progress");
        spinner.addClass("is-active");
        var data = two_get_critical_page_data(el);
        data.task = "generate";
        var two_critical_sizes = two_get_critical_sizes();
        var two_critical_pages = two_two_critical_pages(data['page_id']);
        var ajax_data = {
            'action': 'two_critical',
            'data': data,
            'two_critical_pages': two_critical_pages,
            'two_critical_sizes': two_critical_sizes,
            'nonce': two_admin_vars.ajaxnonce,
        };
        jQuery.ajax({
            type: "POST",
            dataType: 'json',
            url: two_admin_vars.ajaxurl,
            data: ajax_data,
        }).done(function (data) {
            if (data.success) {
                if (_this.hasClass("dashicons-database-add")) {
                    //two_generate_critical_button.removeClass("dashicons-database-add");
                    //two_generate_critical_button.addClass("dashicons-database-view");
                }
            }
        });
    }





    function get_two_new_row_html() {
        var two_new_row_html = '<tr>\n' +
            '                    <td class="two_css_name"><input type="text" value=""></td>\n' +
            '                    <td class="two_url"><input type="text" value=""></td>\n' +
            '                    <td class="two_load_type"><select><option value="Disabled">Disabled</option><option value="Async" selected>Async</option></select></td>\n' +
            '                    <td>\n' +
            '                        <span data-task="save"class="two_edit_element dashicons dashicons-edit-large two_save_element dashicons-saved"></span>\n' +
            '                        <span data-task="duplicate" class="two_duplicate_element dashicons dashicons-admin-page"></span>\n                        ' +
            '                        <span class="two_delete_element dashicons dashicons-trash"></span>\n' +
            '                    </td>\n' +
            '                </tr>';
        return two_new_row_html;
    }

    function get_two_duplicate_html(two_css_name, two_url, two_load_type) {
        var two_new_duplicate_row_html = '<tr>\n' +
            '                    <td class="two_css_name">' + two_css_name + '</td>\n' +
            '                    <td class="two_url">' + two_url + '</td>\n' +
            '                    <td class="two_load_type">' + two_load_type + '</td>\n' +
            '                    <td>\n' +
            '                        <span data-task="edit"class="two_edit_element dashicons dashicons-edit-large"></span>\n' +
            '                        <span data-task="duplicate" class="two_duplicate_element dashicons dashicons-admin-page"></span>\n                        ' +
            '                        <span class="two_delete_element dashicons dashicons-trash"></span>\n' +
            '                    </td>\n' +
            '                </tr>';
        return two_new_duplicate_row_html;
    }

    function get_two_table_critical_html(url, id, title, flag_url) {
        let two_critical_wait_untils = two_critical_options.critical_wait_until;
        let two_uncritical_load_types = two_critical_options.uncritical_load_types;


        let two_critical_default_settings = get_two_critical_default_settings();
        let default_sizes = two_critical_default_settings.default_sizes
        let load_type = two_critical_default_settings.load_type
        let use_uncritical = two_critical_default_settings.use_uncritical
        let wait_until = two_critical_default_settings.wait_until

        let generate_class = ""
        if(two_critical_generation_flag == false && two_table_critical.rows().count()>0){
            generate_class = "two_disabled_timeout";
        }
        var critical_sizes = two_get_critical_sizes();


        let uncritical_load_html = "";

        jQuery.each(two_uncritical_load_types, function (key, val){
            var two_selected_load_type = "";
            if(key === load_type){
                two_selected_load_type = "selected";
            }
            uncritical_load_html += "<option "+two_selected_load_type+" value='"+key+"'>"+val+"</option>";
        });


        let critical_wait_until_html = "";

        jQuery.each(two_critical_wait_untils, function (key, val){
            var two_selected_wait_until = "";
            if(key === wait_until){
                two_selected_wait_until = "selected";
            }
            critical_wait_until_html += " <option "+two_selected_wait_until+" value='"+key+"'>"+val+"</option>";
        });

        var critical_sizes_select = '<select multiple class="two_critical_sizes_select">';
        let selected_size_selected = "";

        jQuery.each(critical_sizes, function (uid, size) {
            selected_size_selected = "";
            if(default_sizes.includes(uid)){
                selected_size_selected = "selected";
            }
            critical_sizes_select += '<option '+selected_size_selected+' data-uid="' + uid + '" data-width="' + size.width + '" data-height="' + size.height + '" data-media="' + size.media + '">' + size.width + '/' + size.height + '</option>'
        });
        critical_sizes_select += "</select>";
        let two_use_uncritical_check = "";
        if(use_uncritical){
            two_use_uncritical_check = "checked";
        }
        let flag_img = "";
        if(flag_url){
            flag_img = '<img src="' + flag_url + '" class="two_flag"/>';
        }

        var two_new_row_html = '<tr data-page_id="'+id+'">\n' +
            '                    <td class="critical_page_url" data-page_url="' + url + '" data-page_id="' + id + '"><a href="' + url + '" target="_blank">' + flag_img + title + '</a></td>\n' +
            '                      <td class="critical_page_sizes">\n' +
            critical_sizes_select +
            '                    </td>\n' +
            '                    <td class="critical_page_load_actions">' +
            '                        <select>'+uncritical_load_html+'</select>\n'+
            '                    </td>\n' +
            '                    <td class="critical_page_wait_until">' +
            '                        <select>'+critical_wait_until_html+'</select>\n' +
            '                    </td>\n' +
            '                    <td class="use_uncritical">\n' +
            '                        <input '+two_use_uncritical_check+' type="checkbox" name="use_uncritical">\n' +
            '                    </td>\n'+
            '                    <td>\n' +
            '                      <span></span>\n' +
            '                    </td>\n'+
            '                    <td>\n' +
            '                        <span data-task="edit" class="two_generate_critical dashicons dashicons-database-add '+generate_class+'"></span>\n' +
            '                        <span class="two_delete_critical_page dashicons dashicons-trash" data-page_id="' + id + '"></span>\n' +
            '                        <span class="spinner"></span>\n' +
            '                        <input type="hidden" value="not_started" class="two_critical_page_status">\n' +
            '                    </td>\n' +
            '                </tr>';
        return two_new_row_html;
    }

    function get_two_table_sizes_html() {
        var size_uid = two_generate_uid();
        var two_new_row_html = '<tr class="' + size_uid + '" data-uid="' + size_uid + '">\n' +
            '                    <td class="critical_page_width"><input class="critical_page_size_input critical_page_width_input" type="number" value=""></td>\n' +
            '                    <td class="critical_page_height"><input class="critical_page_size_input critical_page_height_input" type="number" value=""></td>\n' +
            '                    <td class="critical_size_media"><input type="text" name="critical_size_media" class="critical_size_media_input" value=""></td>\n' +
            '                    <td>' +
            '<span data-task="edit" class="two_add_critical_size dashicons dashicons-saved"></span>' +
            '<span class="two_delete_critical_size dashicons dashicons-trash"></span>' +
            '</td>\n' +
            '                </tr>';
        return two_new_row_html;
    }


    function get_two_critical_default_settings(){
        var selector = "#two_critical_pages .two_critical_defaults";
        var load_type = jQuery(selector).find(".critical_page_load_actions_default select").val();
        var wait_until = jQuery(selector).find(".critical_page_wait_until_default select").val();
        var use_uncritical = jQuery(selector).find(".use_uncritical_default input").is(":checked");
        var default_sizes = jQuery(selector).find(".critical_page_sizes_default select option:selected").map(function () {
            var uid = jQuery(this).data("uid");
            var data = [uid];
            return data;
        }).get();
        return {
            "load_type" : load_type,
            "wait_until" :wait_until,
            "use_uncritical" : use_uncritical,
            "default_sizes": default_sizes,
        }

    }
});


function clear_messages() {
    jQuery(".two_error_message, .two_success_message, .two_webp_success_message, .two_webp_error_message").css({
        'display': 'none'
    });
}

function two_get_critical_page_data(el) {
    var critical_page_url = el.find(".critical_page_url").data("page_url");
    var critical_page_id = el.find(".critical_page_url").data("page_id");
    var critical_page_wait_until = el.find(".critical_page_wait_until select").val();
    var critical_page_sizes = el.find(".critical_page_sizes .two_critical_sizes_select option:selected").map(function () {
        var uid = jQuery(this).data("uid");
        var data = two_get_critical_size(uid);
        return data;
    }).get();
    var return_data = {
        page_url: critical_page_url,
        page_id: critical_page_id,
        page_sizes: critical_page_sizes,
        wait_until: critical_page_wait_until,
        url_query: jQuery("#two_critical_url_args").val(),
    }
    return return_data;
}

function two_get_critical_sizes() {
    var critical_sizes = {};
    jQuery("#two_critical_sizes tbody tr").each(function () {
        var data_id = jQuery(this).data("uid");
        var critical_page_width = jQuery(this).find(".critical_page_width").text();
        var critical_page_height = jQuery(this).find(".critical_page_height").text();
        var critical_size_media = jQuery(this).find(".critical_size_media_input").val();
        if (typeof critical_page_width !== "undefined" && typeof critical_page_height !== "undefined") {
            var data = {
                width: critical_page_width,
                height: critical_page_height,
                media: critical_size_media,
                uid: data_id
            }
            critical_sizes[data_id] = data;
        }
    });
    return critical_sizes;
}

function two_get_critical_size(id) {
    var el = jQuery("." + id);
    var critical_page_width = el.find(".critical_page_width").text();
    var critical_page_height = el.find(".critical_page_height").text();
    var critical_size_media = el.find(".critical_size_media_input").val();
    var data = {
        width: critical_page_width,
        height: critical_page_height,
        media: critical_size_media,
        uid: id
    }
    return data;
}

function two_two_critical_pages(page_id) {
    var two_critical_pages = {};
    var selector = "#two_critical_pages tbody tr";
    if (undefined != page_id) {
        selector += "[data-page_id=" + page_id + "]";
    }
    jQuery(selector).each(function () {
        var critical_page_title = jQuery(this).find(".critical_page_url a").text();
        var critical_page_url = jQuery(this).find(".critical_page_url").data("page_url");
        var critical_page_id = jQuery(this).find(".critical_page_url").data("page_id");
        var two_critical_page_status = jQuery(this).find(".two_critical_page_status").val();
        var load_type = jQuery(this).find(".critical_page_load_actions select").val();
        var wait_until = jQuery(this).find(".critical_page_wait_until select").val();
        var use_uncritical = jQuery(this).find(".use_uncritical input").is(":checked");
        var result = jQuery(this).find(".critical_page_sizes .two_critical_sizes_select option:selected").map(function () {
            var width = jQuery(this).data("width");
            var height = jQuery(this).data("height");
            var uid = jQuery(this).data("uid");
            var media = jQuery("." + uid + " .critical_size_media input").val();
            var data = [uid];
            return data;
        }).get();
        var critical_page_sizes = result;


        if (typeof critical_page_url !== "undefined" && typeof critical_page_sizes !== "undefined") {
            var data = {
                title: critical_page_title,
                url: critical_page_url,
                id: critical_page_id,
                sizes: critical_page_sizes,
                load_type: load_type,
                wait_until: wait_until,
                status: two_critical_page_status,
                use_uncritical: use_uncritical,
            }
            two_critical_pages[critical_page_id] = data;
        }
    });
    return two_critical_pages;
}

function two_generate_uid() {
    var u_date = Date.now();
    var unique_id = "two_" + u_date;
    return unique_id;
}


function two_check_critical_statuses(){
    jQuery.ajax({
        type: "POST",
        dataType: 'json',
        url: two_admin_vars.ajaxurl,
        data: {
            'action': 'two_critical_statuses',
            'nonce': two_admin_vars.ajaxnonce,
        },
    }).done(function (data) {
        if(data.status != "1"){
            two_critical_generation_flag = true;
            jQuery(".two_generate_critical").removeClass("two_disabled_timeout");
        }
        let data_pages = data.pages;
        data_pages.forEach((elem) => {
            let spinner =jQuery("#two_critical_pages tr[data-page_id='"+elem.page_id+"']").find(".spinner");
            let two_generate_critical_button =jQuery("#two_critical_pages tr[data-page_id='"+elem.page_id+"']").find(".two_generate_critical");
            let two_critical_page_status =jQuery("#two_critical_pages tr[data-page_id='"+elem.page_id+"']").find(".two_critical_page_status");
            if(elem.status == "success"){
                jQuery(".two_critical_error").addClass("two_critical_blocked");
                two_generate_critical_button.removeClass("two_disabled");
                spinner.removeClass("is-active");
                two_generate_critical_button.addClass("dashicons-database-view");
                two_generate_critical_button.removeClass("dashicons-database-add");
                two_critical_page_status.val("success");
            }else if(elem.status == "in_progress"){
                spinner.addClass("is-active");
                two_critical_page_status.val("in_progress");
                two_generate_critical_button.addClass("two_disabled");
            }else if(elem.status == "error"){
                jQuery(".two_critical_error").removeClass("two_critical_blocked");
                spinner.removeClass("is-active");
                two_generate_critical_button.addClass("dashicons-database-add");
                two_generate_critical_button.removeClass("dashicons-database-view");
                two_generate_critical_button.removeClass("two_disabled");
                two_critical_page_status.val("error");
            }else{
                spinner.removeClass("is-active");
                two_generate_critical_button.addClass("dashicons-database-add");
                two_generate_critical_button.removeClass("dashicons-database-view");
                two_generate_critical_button.removeClass("two_disabled");
                two_critical_page_status.val("not_started");
            }
        })
    });
}


let two_check_critical_interval = false;
function two_check_critical_restart() {
    if(two_check_critical_interval != false){
        clearInterval(two_check_critical_interval);
    }
    two_check_critical_interval = setInterval(function (){
        two_check_critical_statuses();
    },7000);
}
two_check_critical_restart();


