(function(){
    if (!window.PM_TRACKER) return;
    var rest = window.PM_TRACKER.rest_url;
    var postId = window.PM_TRACKER.post_id;
    var nonce = window.PM_TRACKER.nonce || '';

    function sendEvent(event, label) {
        try {
            fetch(rest, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce
                },
                body: JSON.stringify({ post_id: postId, event: event, label: label })
            }).then(function(resp) {
                // log only for debugging; remove in production
                // console.log('Event sent:', event, label, resp.status);
                return resp.json().catch(function(){ return null; });
            }).then(function(data){
                // optional: handle skipped responses
                if (data && data.skipped) {
                    // skipped due to rate-limit
                }
            }).catch(function(){
                // network or server error
            });
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
