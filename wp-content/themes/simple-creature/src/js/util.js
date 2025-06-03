export function debounce(fn, threshold) {
  var timeout;
  return function debounced() {
    if (timeout) {
      clearTimeout(timeout);
    }
    function delayed() {
      fn();
      timeout = null;
    }
    timeout = setTimeout(delayed, threshold || 100);
  };
}

export function throttle(fn, threshhold, scope) {
  threshhold = "undefined" !== typeof threshhold ? threshhold : 250;
  var last, deferTimer;
  return function () {
    var context = scope || this;

    var now = +new Date(),
      args = arguments;
    if (last && now < last + threshhold) {
      // hold on to it
      clearTimeout(deferTimer);
      deferTimer = setTimeout(function () {
        last = now;
        fn.apply(context, args);
      }, threshhold);
    } else {
      last = now;
      fn.apply(context, args);
    }
  };
}

export function lerp(v0, v1, t) {
  return v0 * (1 - t) + v1 * t;
}

export function scale(opts) {
  var istart = opts.domain[0],
    istop = opts.domain[1],
    ostart = opts.range[0],
    ostop = opts.range[1];

  return function scale(value) {
    return ostart + (ostop - ostart) * ((value - istart) / (istop - istart));
  };
}

export function convertRange(value, r1, r2) {
  return ((value - r1[0]) * (r2[1] - r2[0])) / (r1[1] - r1[0]) + r2[0];
}

export function precisionRound(number, precision) {
  var factor = Math.pow(10, precision);
  return Math.round(number * factor) / factor;
}

export function getRandom(min, max) {
  return Math.random() * (max - min) + min;
}

export function getRandomInt(min, max) {
  min = Math.ceil(min);
  max = Math.floor(max);
  return Math.floor(Math.random() * (max - min + 1)) + min;
}

export function popupwindow(url, title, w, h) {
  var x = window.outerWidth / 2 + window.screenX - w / 2,
    y = window.outerHeight / 2 + window.screenY - h / 2;
  return window.open(
    url,
    title,
    "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=" +
      w +
      ", height=" +
      h +
      ", top=" +
      y +
      ", left=" +
      x
  );
}

export function tweenPromise(tween) {
  return new Promise(function (resolve) {
    tween.eventCallback("onComplete", function () {
      resolve(true);
    });
  });
}

export function videoEmbed(url) {
  var pattern1 = /(?:http?s?:\/\/)?(?:www\.)?(?:vimeo\.com)\/?(.+)/g;
  var pattern2 = /(?:http?s?:\/\/)?(?:www\.)?(?:youtube\.com|youtu\.be)\/(?:watch\?v=)?(.+)/g;
  var pattern3 = /([-a-zA-Z0-9@:%_\+.~#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~#?&//=]*)?(?:jpg|jpeg|gif|png))/gi;
  var replacement;
  var html;

  if (pattern1.test(url)) {
    replacement =
      '<iframe width="420" height="345" src="//player.vimeo.com/video/$1?autoplay=1" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';

    html = url.replace(pattern1, replacement);
  }

  if (pattern2.test(url)) {
    replacement =
      '<iframe width="420" height="345" src="//www.youtube.com/embed/$1?autoplay=1" frameborder="0" allow="autoplay" allowfullscreen></iframe>';
    html = url.replace(pattern2, replacement);
  }

  return html;
}
