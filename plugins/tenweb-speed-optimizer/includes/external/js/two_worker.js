let two_css_length = 0;
let two_connected_css_length = 0;
let two_uncritical_fonts = null;
let two_uncritical_fonts_status = false;
if(two_font_actions == "not_load" || two_font_actions == "exclude_uncritical_fonts"){
    two_uncritical_fonts_status = true;
}

self.addEventListener("message", function(e) {
    two_css_length = e.data.css.length;
    if(!e.data.critical_data.critical_css || !e.data.critical_data.critical_fonts){
        two_uncritical_fonts_status = false;
    }
    if(e.data.font.length>0){
        two_fetch_inbg(e.data.font, "font");
    }
    if(e.data.js.length>0){
        two_fetch_inbg(e.data.js, "js");
    }
    if(e.data.excluded_js.length>0){
        two_fetch_inbg(e.data.excluded_js, "js" , true);
    }
    if(e.data.css.length>0){
        two_fetch_inbg(e.data.css, "css");
    }
}, false);

function two_fetch_inbg(data, type, excluded_js = false) {
    for(let i in data){
        if(typeof data[i].url != "undefined"){
            var modifiedScript = null;
            if(type === "js" && typeof data[i].exclude_blob != "undefined" && data[i].exclude_blob){
                modifiedScript = {
                    id: i,
                    status: 'ok',
                    type: type,
                    url: data[i].url,
                    uid:data[i].uid
                };
                two_send_worker_data(modifiedScript);
                continue;
            }



            fetch(data[i].url, {mode:'no-cors',redirect: 'follow'}).then((r) => {
                if (!r.ok || r.status!==200) {
                    throw Error(r.statusText);
                }
                if(two_uncritical_fonts_status && type== "css"){
                    return (r.text());
                }else{
                    return (r.blob());
                }
            }).then((content_) => {
                let sheetURL = "";
                if(two_uncritical_fonts_status && type == "css"){
                    sheetURL = two_create_blob(content_);
                }else{
                    sheetURL = URL.createObjectURL(content_);
                }
                modifiedScript = null;
                if(type == "css"){
                    modifiedScript = {
                        id: i,
                        type: type,
                        status: 'ok',
                        media: data[i].media,
                        url: sheetURL,
                        uid:data[i].uid,
                        original_url: data[i].url,
                        two_uncritical_fonts:two_uncritical_fonts,
                    };
                }else if(type == "js"){
                    modifiedScript = {
                        id: i,
                        status: 'ok',
                        type: type,
                        url: sheetURL,
                        uid:data[i].uid
                    };
                }else if(type == "font"){
                    modifiedScript = {
                        status: 'ok',
                        type: type,
                        main_url: data[i].url,
                        url:sheetURL,
                        font_face:data[i].font_face
                    };
                }
                if(excluded_js){
                    modifiedScript.excluded_from_delay = true;
                }
                two_send_worker_data(modifiedScript);
            }).catch(function(error) {
                console.log("error in fetching: "+error.toString()+", bypassing "+data[i].url);
                fetch(data[i].url, {redirect: 'follow'}).then((r) => {
                    if (!r.ok || r.status!==200) {
                        throw Error(r.statusText);
                    }
                    if(two_uncritical_fonts_status && type== "css"){
                        return (r.text());
                    }else{
                        return (r.blob());
                    }
                }).then((content_) => {
                    let sheetURL = "";
                    if(two_uncritical_fonts_status && type == "css"){
                        sheetURL = two_create_blob(content_);
                    }else{
                        sheetURL = URL.createObjectURL(content_);
                    }
                    var modifiedScript = null;
                    if(type == "css"){
                        modifiedScript = {
                            id: i,
                            type: type,
                            status: 'ok',
                            media: data[i].media,
                            url: sheetURL,
                            uid:data[i].uid,
                            original_url: data[i].url,
                            two_uncritical_fonts:two_uncritical_fonts,
                        };
                    }else if(type == "js"){
                        modifiedScript = {
                            id: i,
                            status: 'ok',
                            type: type,
                            url: sheetURL,
                            uid:data[i].uid
                        };
                    }else if(type == "font"){
                        modifiedScript = {
                            status: 'ok',
                            type: type,
                            main_url: data[i].url,
                            url:sheetURL,
                            font_face:data[i].font_face
                        };
                    }
                    if(excluded_js){
                        modifiedScript.excluded_from_delay = true;
                    }
                    two_send_worker_data(modifiedScript);
                }).catch(function(error) {
                    console.log("error in fetching no-cors: "+error.toString()+", bypassing "+data[i].url);
                    try {
                        console.log("error in fetching: "+error.toString()+", sending XMLHttpRequest"+data[i].url);
                        let r = new XMLHttpRequest;
                        if(two_uncritical_fonts_status && type== "css"){
                            r.responseType = "text";
                        }else{
                            r.responseType = "blob";
                        }
                        r.onload = function (content_) {
                            let sheetURL = "";
                            if(two_uncritical_fonts_status && type == "css"){
                                sheetURL = two_create_blob(content_.target.response);
                            }else{
                                sheetURL = URL.createObjectURL(content_.target.response);
                            }
                            if(r.status !== 200){
                                two_XMLHttpRequest_error(excluded_js, data[i], type, i);
                                return;
                            }
                            console.log("error in fetching: "+error.toString()+", XMLHttpRequest success "+data[i].url);
                            let modifiedScript = null;
                            if(type == "css"){
                                modifiedScript = {
                                    id: i,
                                    type: type,
                                    status: 'ok',
                                    media: data[i].media,
                                    url: sheetURL,
                                    uid:data[i].uid,
                                    two_uncritical_fonts:two_uncritical_fonts,
                                };
                            }else if(type == "js"){
                                modifiedScript = {
                                    id: i,
                                    type: type,
                                    status: 'ok',
                                    url: sheetURL,
                                    uid:data[i].uid
                                };
                            }else if(type == "font"){
                                modifiedScript = {
                                    type: type,
                                    status: 'ok',
                                    main_url: data[i].url,
                                    url: sheetURL,
                                    font_face:data[i].font_face
                                };
                            }
                            if(excluded_js){
                                modifiedScript.excluded_from_delay = true;
                            }
                            two_send_worker_data(modifiedScript);
                        };
                        r.onerror = function () {
                            two_XMLHttpRequest_error(excluded_js, data[i], type, i)
                        };
                        r.open("GET", data[i].url, true);
                        r.send();

                    } catch (e) {
                        console.log("error in fetching: "+e.toString()+", running fallback for "+data[i].url);
                        var modifiedScript = null;
                        if(type == "css" || type == "js"){
                            modifiedScript = {
                                id: i,
                                type: type,
                                status: 'error',
                                url: data[i].url,
                                uid:data[i].uid
                            };
                        }else if(type == "font"){
                            modifiedScript = {
                                type: type,
                                status: 'error',
                                url: data[i].url,
                                font_face:data[i].font_face
                            };
                        }
                        if(excluded_js){
                            modifiedScript.excluded_from_delay = true;
                        }
                        two_send_worker_data(modifiedScript);
                    }
                });
            });
        }
    }
}


function two_XMLHttpRequest_error(excluded_js , data_i, type, i){
    console.log("error in fetching: XMLHttpRequest failed "+data_i.url);
    var modifiedScript = null;
    if(type == "css" || type == "js"){
        modifiedScript = {
            id: i,
            type: type,
            status: 'error',
            url: data_i.url,
            uid:data_i.uid
        };
    }else if(type == "font"){
        modifiedScript = {
            type: type,
            status: 'error',
            url: data_i.url,
            font_face:data_i.font_face
        };
    }
    if(excluded_js){
        modifiedScript.excluded_from_delay = true;
    }
    two_send_worker_data(modifiedScript);
}

function two_create_blob(str){
    two_uncritical_fonts = "";
    const regex = /@font-face\s*\{(?:[^{}])*\}/sig;
    str = str.replace(regex, function (e){
        if(e.includes("data:application")){
            return e;
        }
        two_uncritical_fonts += e;
        return "";
    });
    let blob_data = new Blob([str], {type : "text/css"});
    let sheetURL = URL.createObjectURL(blob_data);
    return sheetURL;
}

function two_send_worker_data(data){
    if(data.type == "css"){
        two_connected_css_length++;
        data.length = two_css_length;
        data.connected_length = two_connected_css_length;
    }
    self.postMessage(data)
}


