(function(){
  'use strict';

  // Helper: observe elements with [data-slide] and animate when they enter viewport
  var supportsIntersection = 'IntersectionObserver' in window;

  function initElement(el){
    if (!el) return;
    // ensure default CSS variables exist
    if (!el.style.getPropertyValue('--gwt-slide-distance')){
      el.style.setProperty('--gwt-slide-distance', '100%');
    }
    el.classList.add('gwt-slide-init');
  }

  function animateIn(el){
    if (!el) return;
    // Add in-class to trigger transition
    requestAnimationFrame(function(){
      el.classList.add('gwt-slide-in');
    });
  }

  function handleAll(){
    var els = Array.prototype.slice.call(document.querySelectorAll('[data-slide]'));
    if (!els.length) return;
    els.forEach(initElement);

    if (supportsIntersection){
      var io = new IntersectionObserver(function(entries){
        entries.forEach(function(entry){
          if (entry.isIntersecting){
            animateIn(entry.target);
            io.unobserve(entry.target);
          }
        });
      }, { threshold: 0.1 });
      els.forEach(function(e){ io.observe(e); });
    } else {
      // fallback: animate everything after short delay
      setTimeout(function(){ els.forEach(animateIn); }, 60);
    }
  }

  if (document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', handleAll);
  } else {
    handleAll();
  }
})();
