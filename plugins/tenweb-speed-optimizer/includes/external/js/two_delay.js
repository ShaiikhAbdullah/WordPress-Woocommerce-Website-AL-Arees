two_worker_styles_list = [];
two_worker_styles_count = 0;

var two_script_list = typeof two_worker_data_js === "undefined" ? [] : two_worker_data_js.js;
var two_excluded_js_list = typeof two_worker_data_excluded_js === "undefined" ? [] : two_worker_data_excluded_js.js;
var excluded_count = two_excluded_js_list.filter((el) => {return !!el['url']}).length;
var two_css_list = typeof two_worker_data_css === "undefined" ? [] : two_worker_data_css.css;
var two_fonts_list = typeof two_worker_data_font === "undefined" ? [] : two_worker_data_font.font;
var two_critical_data = typeof two_worker_data_critical_data === "undefined" ? [] : two_worker_data_critical_data.critical_data;
var wcode = new Blob([
    document.querySelector("#two_worker").textContent
], { type: "text/javascript" });

var two_worker = new Worker(window.URL.createObjectURL(wcode) );
var two_worker_data = {"js" : two_script_list, "excluded_js":two_excluded_js_list , "css": two_css_list , "font":two_fonts_list, critical_data:two_critical_data}
two_worker.postMessage(two_worker_data);
two_worker.addEventListener("message",function(e) {
    var data = e.data;
    if(data.type === "css" && data.status === "ok"){
        if(data.two_uncritical_fonts && two_font_actions == "exclude_uncritical_fonts"){
            let two_uncritical_fonts = data.two_uncritical_fonts;
            const two_font_tag = document.createElement("style");
            two_font_tag.innerHTML = two_uncritical_fonts;
            two_font_tag.className = "two_uncritical_fonts";
            document.body.appendChild(two_font_tag);
        }
        if(window.two_page_loaded){
            two_connect_style(data);
        }else{
            two_worker_styles_list.push(data);
        }
    } else if(data.type === "js"){
        if(data.status === "ok") {
            if(data.excluded_from_delay){
                two_excluded_js_list[data.id].old_url = two_excluded_js_list[data.id].url;
                two_excluded_js_list[data.id].url = data.url;
                two_excluded_js_list[data.id].success = true;
                excluded_count--;
                if(excluded_count === 0) {
                    two_connect_script(0, two_excluded_js_list)
                }
            }else{
                two_script_list[data.id].old_url = two_script_list[data.id].url;
                two_script_list[data.id].url = data.url;
                two_script_list[data.id].success = true;
            }
        }
    } else if (data.type === "css" && data.status === "error") {
        console.log("error in fetching, connecting style now")
        two_connect_failed_style(data);
    } else if(data.type === "font"){
        two_connect_font(data);
    }
});

function UpdateQueryString(key, value, url) {
    if (!url) url = window.location.href;
    var re = new RegExp("([?&])" + key + "=.*?(&|#|$)(.*)", "gi"),
        hash;

    if (re.test(url)) {
        if (typeof value !== "undefined" && value !== null) {
            return url.replace(re, "$1" + key + "=" + value + "$2$3");
        }
        else {
            hash = url.split("#");
            url = hash[0].replace(re, "$1$3").replace(/(&|\?)$/, "");
            if (typeof hash[1] !== "undefined" && hash[1] !== null) {
                url += "#" + hash[1];
            }
            return url;
        }
    }
    else {
        if (typeof value !== "undefined" && value !== null) {
            var separator = url.indexOf("?") !== -1 ? "&" : "?";
            hash = url.split("#");
            url = hash[0] + separator + key + "=" + value;
            if (typeof hash[1] !== "undefined" && hash[1] !== null) {
                url += "#" + hash[1];
            }
            return url;
        }
        else {
            return url;
        }
    }
}

function two_connect_failed_style(data) {
    var link  = document.createElement("link");
    link.className = "fallback_two_worker";
    link.rel  = "stylesheet";
    link.type = "text/css";

    link.href = data.url;
    link.media = "none";
    link.onload =  function () { if(this.media==="none"){ if (data.media) {this.media=data.media;}else{this.media="all";}console.log(data.media);} if(data.connected_length == data.length && typeof two_replace_backgrounds != "undefined"){two_replace_backgrounds();}; two_styles_loaded()};
    document.getElementsByTagName("head")[0].appendChild(link);
    if(data.connected_length == data.length && typeof two_replace_backgrounds != "undefined"){
        two_replace_backgrounds();
    }
}

function two_connect_style(data, fixed_google_font=false) {

    if(fixed_google_font === false && typeof two_merge_google_fonts !== "undefined" && data['original_url'] && data['original_url'].startsWith('https://fonts.googleapis.com/css') && data['response']){
        data['response'].text().then(function (content){
            content = two_merge_google_fonts(content)

            let blob = new Blob([content], { type: data['response'].type });
            data['url'] = URL.createObjectURL(blob);
            two_connect_style(data, true);
        });

        return;
    }

    var link  = document.createElement("link");
    link.className = "loaded_two_worker";
    link.rel  = "stylesheet";
    link.type = "text/css";
    link.href = data.url;
    link.media = data.media;
    link.onload = function () {if(data.connected_length == data.length && typeof two_replace_backgrounds != "undefined"){two_replace_backgrounds();};two_styles_loaded()};
    link.onerror = function () {two_styles_loaded()};
    document.getElementsByTagName("head")[0].appendChild(link);
}

var two_event ;

function two_connect_script(i, scripts_list=null) {

    if(i === 0 && event){
        two_event = event;
        event.preventDefault();
    }

    if(scripts_list === null){
        scripts_list = two_script_list;
    }

    if(typeof scripts_list[i] !== "undefined"){
        let data_uid =    "[data-two_delay_id=\""+scripts_list[i].uid+"\"]";
        let current_script = document.querySelector(data_uid);

        let script = document.createElement("script");
        script.type = "text/javascript";
        script.async = false;
        if(scripts_list[i].inline){
            // Decode previously encoded script to get unicode characters working.
            var js_code = decodeURIComponent( atob( scripts_list[i].code ) );
            var blob = new Blob([js_code], {type : "text/javascript"});
            scripts_list[i].url = URL.createObjectURL(blob);
        }
        if(current_script != null && typeof scripts_list[i].url != "undefined"){
            script.dataset.src= scripts_list[i].url;
            current_script.parentNode.insertBefore(script, current_script);
            current_script.getAttributeNames().map(
                function (name){
                    let value = current_script.getAttribute(name);
                    try{
                        script.setAttribute(name, value);
                    }catch(error){
                        console.log(error);
                    }
                }
            );
            current_script.remove();
            script.classList.add("loaded_two_worker_js");
            if(typeof scripts_list[i].exclude_blob != "undefined" && scripts_list[i].exclude_blob ){
                script.dataset.blob_exclude = "1";
            }
        }
        i++;
        two_connect_script(i, scripts_list);

    } else {
        let elementor_frontend_js = null;
        document.querySelectorAll(".loaded_two_worker_js").forEach((elem) => {
            let id = elem.getAttribute("id");
            /*We load this JS last so that the all the elementor widgets' js files have already been loaded*/
            if(id != 'elementor-frontend-js') {
                two_load_delayed_js(elem);
            }else{
                elementor_frontend_js = elem;
            }
        });
        if(elementor_frontend_js !== null){
            two_load_delayed_js(elementor_frontend_js);
        }
    }
}
function two_load_delayed_js(elem){
    let data_src = elem.dataset.src;
    if(elem.dataset.blob_exclude === "1"){
        delete elem.dataset.blob_exclude;
        delete elem.dataset.src;
        delete elem.dataset.two_delay_id;
        delete elem.dataset.two_delay_src;
    }
    if (data_src){
        elem.setAttribute("src",data_src);
    }
}

function two_connect_font(data){
    let font_face = data.font_face;

    if(font_face.indexOf("font-display")>=0){
        const regex = /font-display:[ ]*[a-z]*[A-Z]*;/g;
        while ((m = regex.exec(font_face)) !== null) {
            if (m.index === regex.lastIndex) {
                regex.lastIndex++;
            }

            m.forEach((match, groupIndex) => {
                console.log(match);
                font_face.replace(match, "font-display: swap;");
            });
        }
    }else{
        font_face = font_face.replace("}", ";font-display: swap;}");
    }
    if(typeof data.main_url != "undefined"){
        font_face = font_face.replace(data.main_url, data.url);
    }
    var newStyle = document.createElement("style");
    newStyle.className = "two_critical_font";
    newStyle.appendChild(document.createTextNode(font_face));
    document.head.appendChild(newStyle);
}
let connect_stile_timeout = setInterval(function (){
    console.log(window.two_page_loaded);
    if(window.two_page_loaded){
        clearInterval(connect_stile_timeout);
        two_worker_styles_list.forEach(function(item, index){
            two_connect_style(item);
        });
        two_worker_styles_list = [];
    }
},500);
function two_styles_loaded() {
    if (two_css_list.length - ++two_worker_styles_count == 0) {
        var critical_css = document.getElementById( "two_critical_css" );
        if ( critical_css ) {
            critical_css.remove();
        }
        onStylesLoadEvent = new Event( "two_css_loaded" );
        window.dispatchEvent( onStylesLoadEvent );
    }
}
