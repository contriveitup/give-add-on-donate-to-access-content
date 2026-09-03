(function (root) {
  function initSelect2(jquery) {
    if (!jquery || !jquery.fn || typeof jquery.fn.select2 !== "function") {
      return false;
    }

    var $fields = jquery(".select2");

    if (!$fields || !$fields.length) {
      return false;
    }

    $fields.select2({
      width: "resolve",
    });

    return true;
  }

  var isModule = typeof module !== "undefined" && module.exports;

  if (!isModule && typeof jQuery !== "undefined") {
    if (typeof jQuery.noConflict === "function") {
      jQuery.noConflict();
    }

    initSelect2(jQuery);
  }

  var api = {
    initSelect2: initSelect2,
  };

  root.dtacGiveAdmin = api;

  if (typeof module !== "undefined" && module.exports) {
    module.exports = api;
  }
})(typeof window !== "undefined" ? window : globalThis);
