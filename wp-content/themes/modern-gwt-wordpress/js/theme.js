/**
 *
 * GWT theme scripts.
 *
 */

// Cookie handler, non-$ style
// function createCookie(name, value, days) {
//   if (days) {
//     var date = new Date();
//     date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
//     var expires = "; expires=" + date.toGMTString();
//   } else var expires = "";
//   document.cookie = name + "=" + value + expires + "; path=/";
//   console.log(document.cookie);
// }

function createCookie(name, value, days) {
  let cookie = `${name}=${encodeURIComponent(value)}`;

  // Add expiry date
  if (days) {
    const expiry = new Date();
    expiry.setTime(expiry.getTime() + days * 24 * 60 * 60 * 1000);
    cookie += `; expires=${expiry.toUTCString()}`;
  }
  // Add Secure
  cookie += `; secure`;
  cookie += `; HttpOnly`;
  // Set an HTTP cookie
  document.cookie = cookie;
  //console.log(document.cookie);
  //console.log(cookie);
}

function readCookie(name) {
  var nameEQ = name + "=";
  var ca = document.cookie.split(";");
  for (var i = 0; i < ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) === " ") c = c.substring(1, c.length);
    if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
  }
  return null;
}

function eraseCookie(name) {
  createCookie(name, "");
}

function initPreloader() {
  const preloader = document.getElementById("preloader");

  if (!preloader) {
    return;
  }

  const body = document.body;
  const progress = preloader.querySelector(".loading-progress");
  const prefersReducedMotion =
    window.matchMedia &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  const minimumDuration = prefersReducedMotion ? 0 : 3000;
  const fadeDuration = prefersReducedMotion ? 0 : 500;
  let currentProgress = 0;
  let isFinished = false;

  const getStorage = () => {
    try {
      return window.sessionStorage;
    } catch (error) {
      return null;
    }
  };

  const storage = getStorage();
  const hasSeenPreloader =
    document.documentElement.classList.contains("cbc-preloader-skip") ||
    (storage && storage.getItem("cbcPreloaderSeen") === "1");

  const setProgress = (value) => {
    if (!progress) {
      return;
    }

    currentProgress = Math.max(currentProgress, Math.min(100, value));
    progress.style.width = `${currentProgress}%`;
  };

  const markSeen = () => {
    if (!storage) {
      return;
    }

    try {
      storage.setItem("cbcPreloaderSeen", "1");
    } catch (error) {
      // Ignore storage write issues.
    }
  };

  const restoreFocus = () => {
    const target = document.querySelector(
      "#main-content, main, [role='main'], #site-header a[rel='home'], body"
    );

    if (!target || typeof target.focus !== "function") {
      return;
    }

    const hadTabIndex = target.hasAttribute("tabindex");
    if (!hadTabIndex && target !== document.body) {
      target.setAttribute("tabindex", "-1");
    }

    window.requestAnimationFrame(() => {
      try {
        target.focus({ preventScroll: true });
      } catch (error) {
        target.focus();
      }

      if (!hadTabIndex && target !== document.body) {
        target.addEventListener(
          "blur",
          () => target.removeAttribute("tabindex"),
          { once: true }
        );
      }
    });
  };

  const completePreloader = () => {
    if (isFinished) {
      return;
    }

    isFinished = true;
    setProgress(100);
    preloader.classList.add("hidden");
    preloader.setAttribute("aria-hidden", "true");
    body.classList.remove("cbc-preloader-active");
    body.classList.add("cbc-preloader-complete");
    body.setAttribute("aria-busy", "false");
    markSeen();
    restoreFocus();

    window.setTimeout(() => {
      preloader.style.display = "none";
    }, fadeDuration);
  };

  if (hasSeenPreloader) {
    preloader.classList.add("hidden");
    preloader.setAttribute("aria-hidden", "true");
    preloader.style.display = "none";
    body.classList.add("cbc-preloader-complete");
    body.setAttribute("aria-busy", "false");
    return;
  }

  body.classList.add("cbc-preloader-active");
  body.setAttribute("aria-busy", "true");
  setProgress(8);

  const trackableImages = Array.from(document.images).filter(
    (image) => !preloader.contains(image)
  );
  const pendingImages = trackableImages.filter((image) => !image.complete);
  const totalPendingImages = pendingImages.length;
  let loadedImages = 0;

  const syncProgressWithImages = () => {
    if (!totalPendingImages) {
      setProgress(88);
      return;
    }

    const imageRatio = loadedImages / totalPendingImages;
    setProgress(35 + imageRatio * 53);
  };

  pendingImages.forEach((image) => {
    const handleImageSettled = () => {
      loadedImages += 1;
      syncProgressWithImages();
      image.removeEventListener("load", handleImageSettled);
      image.removeEventListener("error", handleImageSettled);
    };

    image.addEventListener("load", handleImageSettled, { once: true });
    image.addEventListener("error", handleImageSettled, { once: true });
  });

  if (document.readyState !== "loading") {
    setProgress(30);
    syncProgressWithImages();
  } else {
    document.addEventListener(
      "DOMContentLoaded",
      () => {
        setProgress(30);
        syncProgressWithImages();
      },
      { once: true }
    );
  }

  const finalize = () => {
    const remaining = Math.max(0, minimumDuration - performance.now());
    window.setTimeout(completePreloader, remaining);
  };

  if (document.readyState === "complete") {
    finalize();
  } else {
    window.addEventListener("load", finalize, { once: true });
  }
}

function initResponsiveSiteHeader() {
  const header = document.getElementById("site-header");
  const headerBar = document.getElementById("site-header-bar");
  const mobileMenuButton = document.getElementById("site-mobile-menu-button");
  const mobileMenuPanel = document.getElementById("site-mobile-menu-panel");

  if (!header || !headerBar || !mobileMenuButton || !mobileMenuPanel) {
    return;
  }

  const desktopBreakpoint = 1024;
  let isTicking = false;

  const syncHeaderHeight = () => {
    document.documentElement.style.setProperty(
      "--gwt-site-header-height",
      `${headerBar.offsetHeight}px`
    );
  };

  const syncScrollState = () => {
    header.classList.toggle("scrolled", window.scrollY > 50);
    syncHeaderHeight();
  };

  const closeMobileMenu = (returnFocus = false) => {
    header.classList.remove("mobile-menu-open");
    mobileMenuButton.setAttribute("aria-expanded", "false");
    mobileMenuPanel.setAttribute("aria-hidden", "true");

    if (returnFocus) {
      mobileMenuButton.focus();
    }
  };

  const openMobileMenu = () => {
    header.classList.add("mobile-menu-open");
    mobileMenuButton.setAttribute("aria-expanded", "true");
    mobileMenuPanel.setAttribute("aria-hidden", "false");
  };

  const toggleMobileMenu = () => {
    if (header.classList.contains("mobile-menu-open")) {
      closeMobileMenu();
      return;
    }

    openMobileMenu();
  };

  syncScrollState();

  mobileMenuButton.addEventListener("click", toggleMobileMenu);

  window.addEventListener(
    "scroll",
    () => {
      if (isTicking) {
        return;
      }

      isTicking = true;

      window.requestAnimationFrame(() => {
        syncScrollState();
        isTicking = false;
      });
    },
    { passive: true }
  );

  window.addEventListener("resize", () => {
    syncHeaderHeight();

    if (window.innerWidth >= desktopBreakpoint) {
      closeMobileMenu();
    }
  });

  document.addEventListener("click", (event) => {
    if (
      window.innerWidth >= desktopBreakpoint ||
      !header.classList.contains("mobile-menu-open")
    ) {
      return;
    }

    if (!header.contains(event.target)) {
      closeMobileMenu();
    }
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && header.classList.contains("mobile-menu-open")) {
      closeMobileMenu(true);
    }
  });

  mobileMenuPanel.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", () => {
      if (window.innerWidth < desktopBreakpoint) {
        closeMobileMenu();
      }
    });
  });
}

initPreloader();

(function (jQuery, Foundation) {
  // Orbit Slider play/pause options
  Foundation.Orbit.defaults.controls = true;
  Foundation.Orbit.defaults.controlClass = "orbit-button-controls";
  Foundation.Orbit.defaults.controlPauseText = "Pause";
  Foundation.Orbit.defaults.controlPlayText = "Play";

  Foundation.Orbit.prototype.initControls = function () {
    var _this = this;
    var statusElement = document.createElement("button");
    var buttonControl = document.createElement("span");
    if (this.options.accessible) {
      var srText = document.createElement("span");
      $(srText).addClass("show-for-sr").text(this.options.controlPauseText);
      $(statusElement).append(srText);
    }
    $(buttonControl).addClass("orbit-button-text").html("&#10073;&#10073;");
    $(statusElement)
      .addClass(this.options.controlClass)
      .append(buttonControl)
      .attr("title", this.options.controlPlayText);
    $(this.$element).prepend(statusElement);
    if (this.options.autoPlay) {
      this.controlPlay();
    }

    this.$button = this.$element.find("." + this.options.controlClass);

    this.$button.on("click.zf.orbit", function () {
      _this.options.pauseOnHover = false;
      _this.$element.off("mouseenter.zf.orbit");
      _this.$element.off("mouseleave.zf.orbit");
      if (_this.options.autoPlay) {
        _this.options.autoPlay = false;
        _this.controlPause();
      } else {
        _this.options.autoPlay = true;
        _this.controlPlay();
      }
    });
  };

  Foundation.Orbit.prototype.controlPause = function () {
      if (!this.timer) return;
    this.timer.restart();
    this.timer.pause();
    this.$wrapper = this.$element.find("." + this.options.controlClass);
    this.$wrapper.attr("title", this.options.controlPlayText);
    this.$buttonText = this.$element.find(
      "." + this.options.controlClass + " .orbit-button-text"
    );
    this.$srText = this.$element.find(
      "." + this.options.controlClass + " .show-for-sr"
    );
    if (this.options.accessible) {
      $(this.$srText).text(this.options.controlPlayText);
    }
    $(this.$buttonText).html("<i class='fa fa-play' aria-hidden='true'></i>");
  };

  Foundation.Orbit.prototype.controlPlay = function () {
      if (!this.timer) return;
    this.timer.restart();
    this.timer.start();
    this.$wrapper = this.$element.find("." + this.options.controlClass);
    this.$wrapper.attr("title", this.options.controlPauseText);
    this.$buttonText = this.$element.find(
      "." + this.options.controlClass + " .orbit-button-text"
    );
    this.$srText = this.$element.find(
      "." + this.options.controlClass + " .show-for-sr"
    );
    if (this.options.accessible) {
      $(this.$srText).text(this.options.controlPauseText);
    }
    $(this.$buttonText).html("<i class='fa fa-pause' aria-hidden='true'></i>");
  };

  $("[data-orbit]").on("init.zf.orbit", function (e) {
    $(e.target).foundation("initControls");
  });

  jQuery(document).ready(function ($) {
    // Transparency Seal
    $("#tp-seal").parent().parent().addClass("text-center");

    // High contrast handler
    if (readCookie("a11y-high-contrast")) {
      $("body").addClass("contrast");
      $("head").append(
        $(
          "<link href='" +
            template_directory +
            "/accessibility/a11y-contrast.css' id='highContrastStylesheet' rel='stylesheet' type='text/css' />"
        )
      );
      $("#accessibility-contrast")
        .attr("aria-checked", true)
        .addClass("active");
    }
    $(".toggle-contrast").on("click", function () {
      if (!$(this).hasClass("active")) {
        $("head").append(
          $(
            "<link href='" +
              template_directory +
              "/accessibility/a11y-contrast.css' id='highContrastStylesheet' rel='stylesheet' type='text/css' />"
          )
        );
        $("body").addClass("contrast");
        // createCookie("a11y-high-contrast", "1");
        $(this).attr("aria-checked", true).addClass("active");
        return false;
      } else {
        $("#highContrastStylesheet").remove();
        $("body").removeClass("contrast");
        $(this).removeAttr("aria-checked").removeClass("active");
        // eraseCookie("a11y-high-contrast");
        return false;
      }
    });

    // Saturation handler
    if (readCookie("a11y-desaturated")) {
      $("body").addClass("desaturated");
      $("head").append(
        $(
          "<link href='" +
            template_directory +
            "/accessibility/a11y-desaturate.css' id='desaturateStylesheet' rel='stylesheet' type='text/css' />"
        )
      );
      $("#accessibility-grayscale")
        .attr("aria-checked", true)
        .addClass("active");
    }
    $(".toggle-grayscale").on("click", function () {
      if (!$(this).hasClass("active")) {
        $("head").append(
          $(
            "<link href='" +
              template_directory +
              "/accessibility/a11y-desaturate.css' id='desaturateStylesheet' rel='stylesheet' type='text/css' />"
          )
        );
        $("body").addClass("desaturated");
        $(this).attr("aria-checked", true).addClass("active");
        createCookie("a11y-desaturated", "1");
        return false;
      } else {
        $("#desaturateStylesheet").remove();
        $("body").removeClass("desaturated");
        $(this).removeAttr("aria-checked").removeClass("active");
        eraseCookie("a11y-desaturated");
        return false;
      }
    });

    // Fontsize handler
    if (readCookie("a11y-larger-fontsize")) {
      $("body").addClass("fontsize");
      $("head").append(
        $(
          "<link href='" +
            template_directory +
            "/accessibility/a11y-fontsize.css' id='fontsizeStylesheet' rel='stylesheet' type='text/css' />"
        )
      );
      $("#accessibility-fontsize")
        .attr("aria-checked", true)
        .addClass("active");
    }
    $(".toggle-fontsize").on("click", function () {
      if (!$(this).hasClass("active")) {
        $("head").append(
          $(
            "<link href='" +
              template_directory +
              "/accessibility/a11y-fontsize.css' id='fontsizeStylesheet' rel='stylesheet' type='text/css' />"
          )
        );
        $("body").addClass("fontsize");
        $(this).attr("aria-checked", true).addClass("active");
        createCookie("a11y-larger-fontsize", "1");
        return false;
      } else {
        $("#fontsizeStylesheet").remove();
        $("body").removeClass("fontsize");
        $(this).removeAttr("aria-checked").removeClass("active");
        eraseCookie("a11y-larger-fontsize");
        return false;
      }
    });

    // Back to top elavator
    var offset = 220;
    var duration = 500;
    $(window).scroll(function () {
      if ($(this).scrollTop() > offset) {
        $("#back-to-top").fadeIn(duration);
      } else {
        $("#back-to-top").fadeOut(duration);
      }
    });
    $("#back-to-top").click(function (event) {
      event.preventDefault();
      $("html, body").animate({ scrollTop: 0 }, duration);
      return false;
    });

    // Skip to Content handler
    // var stc = $('#main-content').position().top;
    // b = $('.sticky ').height();
    // c = stc-58;
    $("#accessibility-skip-content").click(function (event) {
      var stc = $("#main-content").position().top;
      b = $(".sticky ").height();
      c = stc - 58;
      event.preventDefault();
      $("html, body").animate({ scrollTop: c }, duration);
      return false;
    });

    // Skip to Footer handler
    // var stf = $('#gwt-standard-footer').position().top;
    $("#accessibility-skip-footer").click(function (event) {
      var stf = $("#gwt-standard-footer").position().top;
      event.preventDefault();
      $("html, body").animate({ scrollTop: stf }, duration);
      return false;
    });

    // For Testing
    // Adjust Text Sizing
    var zoom = 0;
    $("p").each(function () {
      var el = $(this),
        size = parseInt(el.css("font-size"));
      el.data("font-size", size);
    });
    // For font text default size
    $("#text-default").click(function (event) {
      event.preventDefault();
      zoom = 0;
      $("p").each(function () {
        var el = $(this),
          size = el.data("font-size");
        el.css("font-size", Math.max(size + zoom, 0) + "px");
      });
    });
    // For font text size reducer
    $("#text-reduce").click(function (event) {
      event.preventDefault();
      zoom--;
      $("p").each(function () {
        var el = $(this),
          size = el.data("font-size");
        el.css("font-size", Math.max(size + zoom, 0) + "px");
      });
    });
    // For font text size enlarger
    $("#text-enlarge").click(function (event) {
      event.preventDefault();
      zoom++;
      $("p").each(function () {
        var el = $(this),
          size = el.data("font-size");
        el.css("font-size", Math.max(size + zoom, 0) + "px");
      });
    });
    // End for Adjust Text Sizing

    initResponsiveSiteHeader();


    // End for Testing
  });
})(jQuery, Foundation);
$(document).foundation();
