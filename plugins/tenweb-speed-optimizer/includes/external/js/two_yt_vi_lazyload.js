function replaceEmbedToImage(embedType) {
    var className = (embedType == 'youtube') ? 'yt-lazyload' : 'vi-lazyload';
    var embed = document.querySelectorAll('.' + className),
        embed_observer,
        template_wrap,
        template_content,
        template_playbtn,
        template_logo,
        template_iframe,

        settings_observer_rootMargin    = '200px 0px',                  //Intersection Observer API option - rootMargin (Y, X)
        settings_thumb_base_url         = (embedType == 'youtube') ? 'https://img.youtube.com/vi/' : 'https://raw.githubusercontent.com/the-muda-organization/vimeo-lazyload/master/demo-img/',     //Base URL where thumbnails are stored
        settings_thumb_extension        = 'webp';                       //Thumbnail extension

    if (embed.length > 0) {
        template_wrap = document.createElement('div');
        template_content = document.createElement('div');
        template_playbtn = document.createElement('div');
        template_logo = document.createElement('a');
        template_iframe = document.createElement('iframe');
        template_wrap.classList.add(className + '-wrap');
        template_content.classList.add(className + '-content');
        template_playbtn.classList.add(className + '-playbtn');
        template_logo.classList.add(className + '-logo');
        template_logo.target = '_blank';
        template_logo.rel = 'noreferrer';

        var allow = (embedType == 'youtube') ? 'accelerometer;autoplay;encrypted-media;gyroscope;picture-in-picture' : 'autoplay;fullscreen;picture-in-picture';
        template_iframe.setAttribute('allow', allow);
        template_iframe.setAttribute('allowfullscreen', '');
        embed_observer = new IntersectionObserver(function (elements) {
            elements.forEach(function (e) {
                var this_element = e.target,

                    this_wrap,
                    this_content,
                    this_playbtn,
                    this_logo,
                    this_iframe,
                    this_data_id = e.target.dataset.id,
                    this_data_thumb = e.target.dataset.thumb,
                    this_data_logo = e.target.dataset.logo;
                if (e.isIntersecting === true) {

                    this_wrap = template_wrap.cloneNode();
                    this_element.append(this_wrap);

                    this_content = template_content.cloneNode();
                    this_wrap.append(this_content);
                    var urlProperty = (embedType == 'youtube') ? '--yt-lazyload-img' : '--vi-lazyload-img';
                    if (embedType == 'youtube') {
                        var urlValue = settings_thumb_base_url + this_data_id + this_data_thumb + '/hqdefault.' + settings_thumb_extension;
                        this_content.style.setProperty(urlProperty, 'url("' + urlValue + '")');
                    }else{
                        // todo here we should add viemo thumbnail
                    }

                    this_playbtn = template_playbtn.cloneNode();
                    this_content.append(this_playbtn);

                    if (this_data_logo !== '0') {
                        this_logo = template_logo.cloneNode();
                        this_logo.href = (embedType == 'youtube') ? 'https://youtu.be/' + this_data_id : 'https://vimeo.com/' + this_data_id;
                        this_content.append(this_logo);
                    }
                    this_playbtn.addEventListener('click', function () {
                        this_iframe = template_iframe.cloneNode();
                        this_iframe.src = (embedType == 'youtube') ? 'https://www.youtube.com/embed/' + this_data_id + '?autoplay=1' : 'https://player.vimeo.com/video/' + this_data_id + '?autoplay=1&autopause=0';
                        this_content.append(this_iframe);
                    });
                    embed_observer.unobserve(this_element);
                }
            });

        }, {
            rootMargin: settings_observer_rootMargin,
        });
        embed.forEach(function (e) {
            embed_observer.observe(e);
        });
    }
};

(function () {
    replaceEmbedToImage('youtube');
    replaceEmbedToImage('vimeo');
})();
