(function(){
    if (!window.PM_TRACKER) return;
    var rest = window.PM_TRACKER.rest_url;
    var postId = window.PM_TRACKER.post_id;

    function sendEvent(event, label) {
        try {
            fetch(rest, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ post_id: postId, event: event, label: label })
            }).catch(function(){});
        } catch(e) { }
    }

    // Track view on load
    if (!sessionStorage.getItem('pm_view_' + postId)) {
        sendEvent('view', 'load');
        try { sessionStorage.setItem('pm_view_' + postId, '1'); } catch(e) {}
    }

    // Example: Track clicks on elements with data-pm-event attribute
    document.addEventListener('click', function(e){
        var el = e.target;
        while(el && el !== document) {
            if (el.dataset && el.dataset.pmEvent) {
                sendEvent(el.dataset.pmEvent, el.dataset.pmLabel || '');
                break;
            }
            el = el.parentNode;
        }
    }, false);
})();
