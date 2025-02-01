var two_video_els = document.querySelectorAll(".elementor-widget-video");
for (var i = 0, len = two_video_els.length; i < len; i++) {
        var elementor_widget_video_data = two_video_els[i].getAttribute('data-settings');
        elementor_widget_video_data = JSON.parse(elementor_widget_video_data);
        two_video_els[i].setAttribute('data-settings', "");

        if(typeof elementor_widget_video_data.youtube_url !== "undefined"){
                var video_id = two_getId(elementor_widget_video_data.youtube_url);
                if(video_id){
                        var video_url = "https://www.youtube.com/embed/"+video_id;
                        if(typeof elementor_widget_video_data.autoplay !== "undefined"){
                                video_url+="?autoplay=1";
                        }else{
                                video_url+="?autoplay=0";
                        }
                        if(typeof elementor_widget_video_data.controls !== "undefined"){
                                video_url+="&controls=1";
                        }else{
                                video_url+="&controls=0";
                        }
                        if(typeof elementor_widget_video_data.mute !== "undefined"){
                                video_url+="&mute=1";
                        }else{
                                video_url+="&mute=0";
                        }
                        if(typeof elementor_widget_video_data.modestbranding !== "undefined"){
                                video_url+="&modestbranding=1";
                        }else{
                                video_url+="&modestbranding=0";
                        }
                        if(typeof elementor_widget_video_data.play_on_mobile !== "undefined"){
                                video_url+="&playsinline=1";
                        }else{
                                video_url+="&playsinline=0";
                        }

                        var two_iframe = '<iframe class="yt-lazyload lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" src="" data-src="'+video_url+'" ></iframe>';
                        var elementor_video_tag = two_video_els[i].querySelector(".elementor-video");
                        elementor_video_tag.innerHTML = two_iframe;
                }
        }
}
function two_getId(url) {
        var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
        var match = url.match(regExp);

        if (match && match[2].length == 11) {
                return match[2];
        } else {
                return false;
        }
}
