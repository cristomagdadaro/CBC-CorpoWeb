(function () {
  var header = document.getElementById("site-header");
  var menuToggle = document.getElementById("mbw-menu-toggle");
  var mobileMenu = document.getElementById("mbw-mobile-menu");

  function setScrollState() {
    if (!header) {
      return;
    }
    if (window.scrollY > 80) {
      header.classList.add("is-scrolled");
    } else {
      header.classList.remove("is-scrolled");
    }
  }

  function toggleMobileMenu(forceOpen) {
    if (!menuToggle || !mobileMenu) {
      return;
    }

    var isOpen = typeof forceOpen === "boolean"
      ? forceOpen
      : menuToggle.getAttribute("aria-expanded") !== "true";

    menuToggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
    document.body.classList.toggle("mbw-mobile-open", isOpen);

    if (isOpen) {
      mobileMenu.hidden = false;
    } else {
      mobileMenu.hidden = true;
    }
  }

  if (menuToggle) {
    menuToggle.addEventListener("click", function () {
      toggleMobileMenu();
    });
  }

  if (mobileMenu) {
    mobileMenu.addEventListener("click", function (event) {
      var target = event.target;
      if (target instanceof HTMLElement && target.tagName === "A") {
        toggleMobileMenu(false);
      }
    });
  }

  window.addEventListener("scroll", setScrollState, { passive: true });
  setScrollState();

  var revealItems = document.querySelectorAll(".reveal");
  if ("IntersectionObserver" in window && revealItems.length) {
    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add("is-visible");
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.14 }
    );

    revealItems.forEach(function (item) {
      observer.observe(item);
    });
  } else {
    revealItems.forEach(function (item) {
      item.classList.add("is-visible");
    });
  }

  var researchSearch = document.getElementById("mbw-research-search");
  var cropFilter = document.getElementById("mbw-crop-filter");
  var toolFilter = document.getElementById("mbw-tool-filter");
  var statusFilter = document.getElementById("mbw-status-filter");
  var researchCards = document.querySelectorAll("#mbw-research-grid .mbw-project");

  function filterResearch() {
    if (!researchCards.length) {
      return;
    }

    var query = researchSearch ? researchSearch.value.trim().toLowerCase() : "";
    var crop = cropFilter ? cropFilter.value : "";
    var tool = toolFilter ? toolFilter.value : "";
    var status = statusFilter ? statusFilter.value : "";

    researchCards.forEach(function (card) {
      if (!(card instanceof HTMLElement)) {
        return;
      }
      var title = card.dataset.title || "";
      var cardCrop = card.dataset.crop || "";
      var cardTool = card.dataset.tool || "";
      var cardStatus = card.dataset.status || "";

      var matchesQuery = !query || title.indexOf(query) > -1 || cardCrop.toLowerCase().indexOf(query) > -1 || cardTool.toLowerCase().indexOf(query) > -1;
      var matchesCrop = !crop || crop === cardCrop;
      var matchesTool = !tool || tool === cardTool;
      var matchesStatus = !status || status === cardStatus;

      card.style.display = matchesQuery && matchesCrop && matchesTool && matchesStatus ? "block" : "none";
    });
  }

  [researchSearch, cropFilter, toolFilter, statusFilter].forEach(function (element) {
    if (element) {
      element.addEventListener("input", filterResearch);
      element.addEventListener("change", filterResearch);
    }
  });

  var scientistSearch = document.getElementById("mbw-scientist-search");
  var labFilter = document.getElementById("mbw-lab-filter");
  var scientistCards = document.querySelectorAll("#mbw-scientist-grid .mbw-card");

  function filterScientists() {
    if (!scientistCards.length) {
      return;
    }

    var query = scientistSearch ? scientistSearch.value.trim().toLowerCase() : "";
    var selectedLab = labFilter ? labFilter.value : "";

    scientistCards.forEach(function (card) {
      if (!(card instanceof HTMLElement)) {
        return;
      }

      var name = card.dataset.name || "";
      var specialization = card.dataset.specialization || "";
      var cardLab = card.dataset.lab || "";

      var matchesQuery = !query || name.indexOf(query) > -1 || specialization.indexOf(query) > -1;
      var matchesLab = !selectedLab || selectedLab === cardLab;

      card.style.display = matchesQuery && matchesLab ? "block" : "none";
    });
  }

  [scientistSearch, labFilter].forEach(function (element) {
    if (element) {
      element.addEventListener("input", filterScientists);
      element.addEventListener("change", filterScientists);
    }
  });

  var subscribeForm = document.getElementById("mbw-subscribe-form");
  var subscribeMessage = document.getElementById("mbw-subscribe-message");

  if (subscribeForm) {
    subscribeForm.addEventListener("submit", function (event) {
      event.preventDefault();
      if (!subscribeMessage) {
        return;
      }

      subscribeMessage.textContent = "Thanks. You are now subscribed to CBC updates.";
      subscribeForm.reset();

      window.setTimeout(function () {
        subscribeMessage.textContent = "";
      }, 3200);
    });
  }
})();
