const { describe, test } = require("node:test");
const assert = require("node:assert/strict");
const path = require("path");

function loadAdmin(jquery) {
  const previous = {
    jQuery: global.jQuery,
    window: global.window,
    dtacGiveAdmin: global.dtacGiveAdmin,
  };

  global.window = global;
  global.jQuery = jquery;
  delete require.cache[require.resolve("../../assets-src/js/main.js")];
  const api = require("../../assets-src/js/main.js");

  return {
    api,
    restore() {
      global.jQuery = previous.jQuery;
      global.window = previous.window;
      global.dtacGiveAdmin = previous.dtacGiveAdmin;
    },
  };
}

describe("admin Select2 bootstrap", () => {
  test("does not throw when jQuery is missing", () => {
    const loaded = loadAdmin(undefined);

    assert.equal(typeof loaded.api.initSelect2, "function");
    assert.equal(loaded.api.initSelect2(undefined), false);

    loaded.restore();
  });

  test("does not throw when select2 is not a function", () => {
    const jquery = function fakeJQuery() {
      return { length: 1 };
    };
    jquery.fn = {};

    const loaded = loadAdmin(jquery);

    assert.equal(loaded.api.initSelect2(jquery), false);

    loaded.restore();
  });

  test("skips initialization when no .select2 fields exist", () => {
    const calls = [];
    const jquery = function fakeJQuery(selector) {
      calls.push(selector);
      return { length: 0 };
    };
    jquery.fn = {
      select2() {
        throw new Error("select2 should not run without matching fields");
      },
    };

    const loaded = loadAdmin(jquery);

    assert.equal(loaded.api.initSelect2(jquery), false);
    assert.deepEqual(calls, [".select2"]);

    loaded.restore();
  });

  test("initializes matching Select2 fields", () => {
    const optionsSeen = [];
    const fields = {
      length: 2,
      select2(options) {
        optionsSeen.push(options);
      },
    };
    const jquery = function fakeJQuery() {
      return fields;
    };
    jquery.fn = {
      select2() {},
    };

    const loaded = loadAdmin(jquery);

    assert.equal(loaded.api.initSelect2(jquery), true);
    assert.deepEqual(optionsSeen, [{ width: "resolve" }]);

    loaded.restore();
  });

  test("source file lives next to the plugin JS entry", () => {
    assert.equal(
      path.basename(require.resolve("../../assets-src/js/main.js")),
      "main.js",
    );
  });
});
