(function(){
    if (!window.PM_TRACKER) {
        return;
    }

    var restUrl = window.PM_TRACKER.rest_url;
    var shareUrl = window.PM_TRACKER.share_url || window.PM_TRACKER.ajax_url;
    var postId = window.PM_TRACKER.post_id;
    var nonce = window.PM_TRACKER.nonce || '';
    var shareNonce = window.PM_TRACKER.share_nonce || '';

    function sendEvent(eventName, label, sourceEl) {
        var targetPostId = postId;
        if (sourceEl && sourceEl.dataset && sourceEl.dataset.postId) {
            targetPostId = sourceEl.dataset.postId;
        }

        if (eventName === 'view' || !targetPostId) {
            return;
        }

        try {
            if (eventName === 'share') {
                var shareBody = new URLSearchParams();
                shareBody.append('action', 'pm_share');
                shareBody.append('nonce', shareNonce);
                shareBody.append('post_id', String(targetPostId));

                fetch(shareUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                    },
                    body: shareBody.toString()
                }).catch(function(){});
                return;
            }

            fetch(restUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce
                },
                body: JSON.stringify({ post_id: targetPostId, event: eventName, label: label })
            }).catch(function(){});
        } catch (e) {}
    }

    document.addEventListener('click', function(e){
        var el = e.target;
        while (el && el !== document) {
            if (el.dataset && el.dataset.pmEvent) {
                sendEvent(el.dataset.pmEvent, el.dataset.pmLabel || '', el);
                break;
            }
            el = el.parentNode;
        }
    }, false);

    document.addEventListener('DOMContentLoaded', function() {
        var shareButtons = document.querySelectorAll('.share-button');
        shareButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                sendEvent('share', 'share-button-click', button);
            });
        });
    });
})();
