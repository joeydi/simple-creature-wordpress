import jQuery from "jquery";

import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
import { ScrollSmoother } from "gsap/ScrollSmoother";
import "lazysizes";
import fitvids from "fitvids";
import { throttle, lerp, precisionRound } from "./util";
import "magnific-popup";
import Flickity from "flickity";
import jQueryBridget from "jquery-bridget";

// Assign jQuery to $ global
Object.assign(window, { $: jQuery, jQuery });

// Wire up Flickity as jQuery plugin
jQueryBridget("flickity", Flickity, jQuery);

// Register gsap plugins
gsap.registerPlugin(ScrollTrigger, ScrollSmoother);

var DL = DL || {};

DL.mq = {
  sm: "(min-width: 576px)",
  md: "(min-width: 768px)",
  lg: "(min-width: 992px)",
  xl: "(min-width: 1200px)",
  xxl: "(min-width: 1440px)",
};

DL.to_animate = [];

DL.scrollToHash = function () {
  // Scroll to anchor if present
  if (window.location.hash) {
    var hash = window.location.hash,
      target = $(hash),
      top;

    if (target.length) {
      top = target.position().top;
      $(window).scrollTop(top);
    }
  }
};

DL.initHeader = function () {
  var html = $("html"),
    toggle = $(".menu-toggle"),
    submenu_items = $("li.menu-item-has-children"),
    submenu_links = submenu_items.find("a");

  toggle.on("click", function () {
    html.toggleClass("menu-active");
  });

  submenu_items.on("mouseenter", function () {
    $(this).addClass("hover");
  });

  submenu_items.on("mouseleave", function () {
    $(this).removeClass("hover");
  });

  submenu_links.on("click", function () {
    $(this).trigger("blur");
    submenu_items.removeClass("hover");
  });
};

DL.scrollTo = function (y, immediately) {
  if (DL.isDesktop) {
    $(window).scrollTop(y);
  } else {
    $(".scroll-wrap").scrollTop(y);
  }

  if (immediately) {
    DL.scrollPosition = y;
  }
};

DL.isDesktop = window.matchMedia(DL.mq.md).matches;
DL.scroll = {
  y: window.scrollY,
  direction: "down",
};
DL.scrollPosition = 0;
DL.initialScroll = true;
DL.initSmoothScrolling = function () {
  ScrollSmoother.create({
    smooth: 1,
    effects: false,
    smoothTouch: 0.1,
  });
};

DL.initScrollAnimations = function () {
  var triggerSelector = [".animate", ".animate-children > *"];

  ScrollTrigger.batch(triggerSelector.join(", "), {
    start: "top bottom",
    end: "bottom top",
    interval: 0.125,
    batchMax: 64,
    onEnter: function (batch) {
      gsap.to(batch, {
        opacity: 1,
        y: 0,
        duration: 2,
        ease: "expo.out",
        stagger: 0.5 / batch.length,
        overwrite: true,
      });
    },
  });
};

DL.initScrollObservers = function () {
  var elements = document.querySelectorAll(".animate, .animate-children > *, .animate, .animate-children > *");

  var observer = new IntersectionObserver(
    function (entries, self) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          DL.to_animate.push(entry.target);
          self.unobserve(entry.target);
        }
      });
    },
    {
      threshold: 0,
    }
  );

  $.each(elements, function (i, el) {
    observer.observe(el);
  });
};

DL.initNestedLinks = function () {
  $("body").on("mouseenter focus", "[data-href]", function () {
    var link = $(this).closest("a"),
      originalHref = link.attr("href"),
      newHref = $(this).data("href");

    link.data("originalHref", originalHref);
    link.attr("href", newHref);
  });

  $("body").on("mouseout blur", "[data-href]", function () {
    var link = $(this).closest("a"),
      originalHref = link.data("originalHref");

    link.attr("href", originalHref);
  });

  $("body").on("keyup", "[data-href]", function (e) {
    var link = $(this).closest("a");

    if (e.which == 13) {
      link[0].click();
    }
  });
};

DL.initLoadMoreLinks = function () {
  $("body").on("click", ".load-more a", function (e) {
    e.preventDefault();

    var link = $(this);
    link.fadeTo(0.5, 0.5);

    $.get(this.href, function (data) {
      var posts = $(data).find("section.blog .posts").children();

      $(".load-more").remove();
      $("section.blog .posts").append(posts);

      DL.initScrollObservers();
    });
  });
};

DL.initShareLinks = function () {
  $(document).on("click", ".share a", function (e) {
    e.preventDefault();
    var url = $(this).attr("href");
    popupwindow(url, "Share", 600, 460);
  });
};

DL.initHero = function () {
  var hero = $("section.image_hero, section.blog_hero");

  if (!hero.length) {
    return;
  }

  // Add loaded class once all backgrounds are loaded
  hero.imagesLoaded(function () {
    hero.addClass("loaded");
  });
};

DL.initPopupVideos = function () {
  const videos = $("a.popup-video");

  if (!videos.length) {
    return;
  }

  videos.magnificPopup({
    type: "iframe",
    mainClass: "mfp-fade",
    removalDelay: 510,
    closeBtnInside: false,
  });
};

DL.initActiveNav = function () {
  var currentUrl = window.location.href.replace(window.location.hash, "");

  // Remove the active class from other links
  $("header li").removeClass("current-menu-item");

  // Add the active class to header link with same href
  $('header a[href="' + currentUrl + '"]')
    .parent()
    .addClass("current-menu-item");
};

DL.initGravityForms = function () {
  var forms = $("div.gform");

  forms.each(function () {
    var form = $(this),
      id = form.data("id"),
      field_values = form.data("field_values");

    $.get(
      DL.ajaxurl,
      {
        action: "load_gravity_form",
        id: id,
        field_values: field_values,
      },
      function (data) {
        form.html(data);
        form.trigger("load_gravity_form");

        if (window.gformInitDatepicker) {
          gformInitDatepicker();
        }
      }
    );
  });

  // $(document).on("gform_post_render", function () {
  //     window.setTimeout(function () {
  //         ScrollTrigger.refresh();
  //     }, 100);
  // });
};

DL.init_testimonials = function () {
  var containers = $("section.testimonials");

  if (!containers.length) {
    return;
  }

  containers.each(function () {
    var container = $(this),
      slider = container.find(".slider");

    // Only init slider if necessary
    if (slider.children().length < 2) {
      return;
    }

    slider.flickity({
      adaptiveHeight: true,
      wrapAround: true,
      pageDots: false,
      prevNextButtons: true,
      arrowShape: "M64.308 2.587L42.896 50l21.412 47.413L58.581 100 36 50 58.58 0z",
    });
  });
};

DL.init_testimonials_slider = function () {
  var containers = $("section.testimonials-slider");

  if (!containers.length) {
    return;
  }

  containers.each(function () {
    var container = $(this),
      imageSlider = container.find(".image-slider"),
      testimonialSlider = container.find(".testimonial-slider");

    if (imageSlider.children().length > 2) {
      imageSlider.flickity({
        adaptiveHeight: true,
        wrapAround: true,
        pageDots: false,
        prevNextButtons: false,
        asNavFor: testimonialSlider[0],
      });
    }

    if (testimonialSlider.children().length > 2) {
      testimonialSlider.flickity({
        cellSelector: ".testimonial",
        adaptiveHeight: true,
        wrapAround: true,
        pageDots: false,
        prevNextButtons: true,
        arrowShape:
          "M49.925.79a2.83 2.83 0 014.053 3.947l-.13.133L6.913 50l46.935 45.13a2.83 2.83 0 01.204 3.863l-.125.138a2.83 2.83 0 01-3.864.204l-.138-.125L.87 52.04a2.83 2.83 0 01-.129-3.948l.129-.132L49.925.79z",
      });
    }
  });
};

DL.init_testimonial_slider_block = function () {
  var sliders = $(".block-testimonial-slider");

  if (!sliders.length) {
    return;
  }

  sliders.each(function () {
    var slider = $(this);

    if (slider.children().length > 2) {
      slider.flickity({
        cellSelector: ".testimonial",
        adaptiveHeight: true,
        wrapAround: true,
        pageDots: false,
        prevNextButtons: true,
        arrowShape: "m69.167 100-50-50 50-50 11.666 11.667L42.5 50l38.333 38.333z",
      });
    }
  });
};

DL.init_team_members = function () {
  const members = $(".member");

  if (!members.length) {
    return;
  }

  members.magnificPopup({
    type: "inline",
    mainClass: "mfp-fade",
    removalDelay: 500,
    closeBtnInside: false,
    fixedContentPos: true,
    gallery: {
      enabled: true,
    },
  });
};

DL.init_featured_projects = function () {
  var containers = $(".featured-projects, .block-featured-projects");

  if (!containers.length) {
    return;
  }

  containers.each(function () {
    var container = $(this),
      slider = container.find(".slider"),
      excerpts = slider.find("a.excerpt");

    // Disable tabbing to unselected slides
    slider.on("select.flickity", function (event, index) {
      excerpts.attr("tabindex", "-1");
      excerpts.eq(index).attr("tabindex", null);
    });

    slider.flickity({
      adaptiveHeight: true,
      wrapAround: true,
      pageDots: false,
      prevNextButtons: true,
      arrowShape:
        "M49.925.79a2.83 2.83 0 014.053 3.947l-.13.133L6.913 50l46.935 45.13a2.83 2.83 0 01.204 3.863l-.125.138a2.83 2.83 0 01-3.864.204l-.138-.125L.87 52.04a2.83 2.83 0 01-.129-3.948l.129-.132L49.925.79z",
    });
  });
};

DL.initFitvids = function () {
  fitvids();
};

jQuery(document).ready(function () {
  DL.initHeader();
  DL.initSmoothScrolling();
  DL.initScrollAnimations();
  DL.initNestedLinks();
  DL.initLoadMoreLinks();
  DL.initShareLinks();
  DL.initActiveNav();
  DL.initGravityForms();
  DL.initScrollObservers();
  DL.initHero();
  DL.initPopupVideos();
  DL.init_testimonials();
  DL.init_testimonials_slider();
  DL.init_testimonial_slider_block();
  DL.init_team_members();
  DL.init_featured_projects();
  DL.initFitvids();
});
