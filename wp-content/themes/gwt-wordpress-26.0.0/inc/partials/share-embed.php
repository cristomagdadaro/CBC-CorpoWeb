<?php
/**
 * Share/embed template partial
 * Usage: include locate_template('inc/partials/share-embed.php');
 * Expects global $post to be available.
 */
if ( ! isset( $post ) ) {
    global $post;
}
if ( ! isset( $post ) ) {
    return;
}
$url = get_permalink( $post );
$title = get_the_title( $post );
$excerpt = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_trim_words( strip_tags( $post->post_content ), 30 );
$encoded_url = rawurlencode( $url );
$encoded_title = rawurlencode( $title );
$encoded_excerpt = rawurlencode( $excerpt );
?>
<div class="gwt-share-btns-container bg-gradient-to-r to-[#a2b917] from-[#1f5d2b] p-2 md:p-3 text-white" data-post-id="<?php echo esc_attr( $post->ID ); ?>">
  <div class="gwt-share-buttons drop-shadow-md flex items-center w-full" role="group" aria-label="Share">
    <p class="flex items-center whitespace-nowrap">Share via </p>
    <button class="gwt-share-btn gwt-share-facebook" data-share="facebook" data-pm-label="shortcode-share" data-pm-event="share" title="Share to Facebook">
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="h-auto w-3 md:w-6 text-white" viewBox="0 0 16 16">
            <path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951"></path>
        </svg>
    </button>
    <button class="gwt-share-btn gwt-share-twitter" data-share="twitter" data-pm-label="shortcode-share" data-pm-event="share" title="Share to X (Twitter)">
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="h-auto w-3 md:w-6 text-white" viewBox="0 0 16 16">
            <path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/>
        </svg>
    </button>
    <button class="gwt-share-btn gwt-share-linkedin" data-share="linkedin" data-pm-label="shortcode-share" data-pm-event="share" title="Share to LinkedIn">
         <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="h-auto w-3 md:w-6 text-white" viewBox="0 0 16 16">
            <path d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854zm4.943 12.248V6.169H2.542v7.225zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248S2.4 3.226 2.4 3.934c0 .694.521 1.248 1.327 1.248zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016l.016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225z"/>
        </svg>
    </button>
    <button class="gwt-share-btn gwt-share-copy" data-share="copy" data-pm-label="shortcode-share" data-pm-event="share" title="Copy link">
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="h-auto w-3 md:w-6 text-white" viewBox="0 0 16 16">
            <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1z"/>
            <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0z"/>
        </svg>
    </button>
    <button class="gwt-share-btn gwt-share-embed" data-share="embed" data-pm-label="shortcode-share" data-pm-event="share" title="Get embed code">
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="h-auto w-3 md:w-6 text-gray-900" viewBox="0 0 16 16">
            <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2z"/>
            <path d="M6.854 4.646a.5.5 0 0 1 0 .708L4.207 8l2.647 2.646a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 0 1 .708 0m2.292 0a.5.5 0 0 0 0 .708L11.793 8l-2.647 2.646a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708 0"/>
        </svg>
    </button>
    <input type="text" data-slide="left" class="gwt-share-embed-data" style="margin: 0;" value="<?php echo esc_url( $url ); ?>" />
  </div>
</div>

<script>(function(){
  function openPopup(url, w, h) {
    var left = (screen.width/2)-(w/2);
    var top = (screen.height/2)-(h/2);
    window.open(url, 'sharewindow', 'toolbar=0,status=0,width='+w+',height='+h+',top='+top+',left='+left);
  }

  document.addEventListener('click', function(e){
    var btn = e.target.closest && e.target.closest('.gwt-share-btn');
    if (!btn) return;
    e.preventDefault();
      var container = btn.closest('.gwt-share-btns-container');
    var postId = container ? container.getAttribute('data-post-id') : null;
    var permalink = '<?php echo esc_js( rawurlencode( $url ) ); ?>';
    var title = '<?php echo esc_js( rawurlencode( $title ) ); ?>';
    var excerpt = '<?php echo esc_js( rawurlencode( $excerpt ) ); ?>';
    var action = btn.getAttribute('data-share');
    if (action === 'facebook') {
      // Facebook share dialog URL (sharer.php)
      var shareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + permalink;
      openPopup(shareUrl, 640, 480);
    } else if (action === 'twitter') {
      var shareUrl = 'https://twitter.com/intent/tweet?url=' + permalink + '&text=' + title;
      openPopup(shareUrl, 640, 400);
    } else if (action === 'linkedin') {
      var shareUrl = 'https://www.linkedin.com/sharing/share-offsite/?url=' + permalink;
      openPopup(shareUrl, 720, 600);
    } else if (action === 'copy') {
      var input = container ? container.querySelector('.gwt-share-embed-data') : null;
      var value = decodeURIComponent(permalink);
      if (input) {
        input.value = value;
        input.classList.add('visible');
        try { input.focus(); input.select(); } catch (e) { /* ignore selection errors */ }
      }
      if (navigator.clipboard && clipboardWriteSupported()) {
        navigator.clipboard.writeText(value).catch(function(){ /* ignore */ });
      }
    } else if (action === 'embed') {
      // Responsive embed wrapper: 16:9 by default (padding-bottom:56.25%)
      var embedHtml = '<div class="gwt-embed-wrapper" style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;max-width:100%;">' +
                      '<iframe src="' + decodeURIComponent(permalink) + '" style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;" frameborder="0" scrolling="no" allowfullscreen></iframe>' +
                      '</div>';
      var input2 = container ? container.querySelector('.gwt-share-embed-data') : null;
      if (input2) {
        input2.value = embedHtml;
        input2.classList.add('visible');
        try { input2.focus(); input2.select(); } catch (e) { /* ignore */ }
      }
      if (navigator.clipboard && clipboardWriteSupported()) {
        navigator.clipboard.writeText(embedHtml).catch(function(){ /* ignore */ });
      }
    }
  }, false);

  function clipboardWriteSupported(){
    try { return !!navigator.clipboard && !!navigator.clipboard.writeText; } catch(e){ return false; }
  }
})();</script>
<style>
  /* hide input by default, show when .visible is present */
  .gwt-share-embed-data{display:none;width:100%;padding:8px;border:1px solid #ddd;margin-top:8px}
  .gwt-share-embed-data.visible{display:block}
</style>
<style>
.gwt-share-embed{border:1px solid #e6e6e6;padding:12px;}
.gwt-share-buttons{display:flex;gap:8px}
.gwt-share-btn{background:#f5f5f5;border:1px solid #ddd;padding:8px 10px;cursor:pointer}
.gwt-share-btn:hover{background:#eee}
.gwt-share-facebook{color:#fff;background:#1877f2;border-color:#1877f2}
.gwt-share-twitter{color:#fff;background:#1da1f2;border-color:#1da1f2}
.gwt-share-linkedin{color:#fff;background:#0a66c2;border-color:#0a66c2}
.gwt-share-copy{background:#62ad84ff; vertical-align: middle; display: flex; align-items: center; justify-content: center; border-color:#62ad84ff; color:#fff;}
  /* Responsive embed wrapper (used by copyable embed snippet) */
  .gwt-embed-wrapper{position:relative;padding-bottom:56.25%;height:0;overflow:hidden;max-width:100%;}
  .gwt-embed-wrapper iframe{position:absolute;top:0;left:0;width:100%;height:100%;border:0}

</style>
