
window.addEventListener("load", function () {
    window.two_page_loaded = true;
});

function logLoaded() {
    console.log("window is loaded");
}

(function listen () {
    if (window.two_page_loaded) {
        logLoaded();
    } else {
        console.log("window is notLoaded");
        window.setTimeout(listen, 50);
    }
})();

function applyElementorControllers (){
    // Important line, because if there is no widget with JS, there is no elementorFrontend instance
    if (!window.elementorFrontend) return;
    // Where the magic hapen
    window.elementorFrontend.init()
}

//Bonus to apply css of the loaded page
function applyViewCss (cssUrl) {
    if (!cssUrl) return;
    const cssNode = document.createElement('link');
    cssNode.setAttribute("href", cssUrl);
    cssNode.setAttribute("rel", "stylesheet");
    cssNode.setAttribute("type", "text/css");
    document.head.appendChild(cssNode);

}

var two_scripts_load = true;
var two_load_delayed_javascript = function (event) {
    if(two_scripts_load){
        two_scripts_load = false;
        two_connect_script(0);
        if(typeof two_delay_custom_js_new == "object"){
            document.dispatchEvent(two_delay_custom_js_new)
        }
        window.two_delayed_loading_events.forEach(function (event) {
            console.log("removed event listener");
            document.removeEventListener(event, two_load_delayed_javascript, false)
        });
    }
};
function two_loading_events(event){
    setTimeout(function(event) {
        return function() {
            var t = function(eventType, elementClientX, elementClientY) {
                var _event = new Event(eventType, {
                    bubbles: true,
                    cancelable: true
                });
                if (eventType === "click") {
                    _event.clientX = elementClientX;
                    _event.clientY = elementClientY
                } else {
                    _event.touches = [{
                        clientX: elementClientX,
                        clientY: elementClientY
                    }]
                }
                return _event
            };
            var element;
            if (event && event.type === "touchend") {
                var touch = event.changedTouches[0];
                element = document.elementFromPoint(touch.clientX, touch.clientY);
                element.dispatchEvent(t('touchstart', touch.clientX, touch.clientY));
                element.dispatchEvent(t('touchend', touch.clientX, touch.clientY));
                element.dispatchEvent(t('click', touch.clientX, touch.clientY));
            } else if (event && event.type === "click") {
                element = document.elementFromPoint(event.clientX, event.clientY);
                element.dispatchEvent(t(event.type, event.clientX, event.clientY));
            }
        }
    }(event), 150);


}