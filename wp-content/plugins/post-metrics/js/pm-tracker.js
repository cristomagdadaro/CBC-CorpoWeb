(function(){
    if (!window.PM_TRACKER) return;
    var restUrl = window.PM_TRACKER.rest_url;
    var shareUrl = window.PM_TRACKER.share_url;
    var postId = window.PM_TRACKER.post_id;
    var nonce = window.PM_TRACKER.nonce || '';

    function sendEvent(event, label) {
        // If no post ID is provided, try to find it from the element's data attributes
        var targetPostId = postId;
        if (event.target && event.target.dataset.postId) {
            targetPostId = event.target.dataset.postId;
        }

        // Do not send 'view' events from the frontend anymore
        if (event === 'view') {
            return;
        }

        var endpoint = (event === 'share') ? shareUrl : restUrl;
        var headers = {
            'Content-Type': 'application/json'
        };

        // Only add the nonce for authenticated endpoints
        if (event !== 'share') {
            headers['X-WP-Nonce'] = nonce;
        }

        try {
            fetch(endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: headers,
                body: JSON.stringify({ post_id: targetPostId, event: event, label: label })
            }).then(function(resp) {
                // log only for debugging; remove in production
                console.log('Event sent:', event, label, resp.status);
                return resp.json().catch(function(){ return null; });
            }).then(function(data){
                // optional: handle skipped responses
                if (data && data.skipped) {
                    // skipped due to rate-limit
                }
                console.log(data);
            }).catch(function(e){
                // network or server error
                console.error(e);
            });
        } catch(e) { }
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

    // Track clicks on share buttons
    document.addEventListener('DOMContentLoaded', function() {
        // Add event listener for share buttons
        var shareButtons = document.querySelectorAll('.share-button'); // Adjust this selector to match your share buttons
        shareButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                sendEvent('share', 'share-button-click');
            });
        });
    });
})();
