const { describe, test } = require("node:test");
const assert = require("node:assert/strict");

function loadBlocks(wp) {
  const previous = {
    wp: global.wp,
    window: global.window,
    dtacGiveBlocks: global.dtacGiveBlocks,
    dtacGiveBlocksUi: global.dtacGiveBlocksUi,
  };

  global.window = global;
  global.wp = wp;
  delete require.cache[require.resolve("../../assets-src/js/blocks.js")];
  const api = require("../../assets-src/js/blocks.js");

  return {
    api,
    restore() {
      global.wp = previous.wp;
      global.window = previous.window;
      global.dtacGiveBlocks = previous.dtacGiveBlocks;
      global.dtacGiveBlocksUi = previous.dtacGiveBlocksUi;
    },
  };
}

describe("restricted content block helpers", () => {
  test("does not throw when wp is missing", () => {
    const loaded = loadBlocks(undefined);

    assert.deepEqual(loaded.api.registerBlocks(undefined), []);
    assert.deepEqual(loaded.api.registered, []);

    loaded.restore();
  });

  test("normalizeFormId rejects junk and zero", () => {
    const loaded = loadBlocks(undefined);

    assert.equal(loaded.api.normalizeFormId("42"), 42);
    assert.equal(loaded.api.normalizeFormId("0"), 0);
    assert.equal(loaded.api.normalizeFormId("-3"), 0);
    assert.equal(loaded.api.normalizeFormId("abc"), 0);
    assert.equal(loaded.api.normalizeFormId(undefined), 0);

    loaded.restore();
  });

  test("normalizeShow only accepts form or message", () => {
    const loaded = loadBlocks(undefined);

    assert.equal(loaded.api.normalizeShow("message"), "message");
    assert.equal(loaded.api.normalizeShow("form"), "form");
    assert.equal(loaded.api.normalizeShow("popup"), "form");
    assert.equal(loaded.api.normalizeShow(""), "form");

    loaded.restore();
  });

  test("getFormOptions falls back to the default form", () => {
    const loaded = loadBlocks(undefined);

    assert.deepEqual(loaded.api.getFormOptions(undefined), [
      { label: "Use default form", value: "0" },
    ]);

    loaded.restore();
  });

  test("getFormOptions uses localized Give forms", () => {
    const loaded = loadBlocks(undefined);
    const forms = [
      { label: "Use default form", value: "0" },
      { label: "General Donation (#7)", value: "7" },
    ];

    assert.deepEqual(loaded.api.getFormOptions({ forms }), forms);

    loaded.restore();
  });

  test("registers both block names with Gutenberg", () => {
    const registered = [];
    const wp = {
      blocks: {
        registerBlockType(name, settings) {
          registered.push({ name, settings });
        },
      },
      element: {
        createElement() {
          return null;
        },
        Fragment: "Fragment",
      },
      blockEditor: {},
      components: {
        PanelBody: "PanelBody",
        SelectControl: "SelectControl",
      },
      i18n: {
        __(text) {
          return text;
        },
      },
    };

    const loaded = loadBlocks(wp);
    const names = loaded.api.registerBlocks(wp);

    assert.deepEqual(names, [
      "dtac/restricted-content",
      "dtac/my-unlocked-content",
    ]);
    assert.deepEqual(
      registered.map((block) => block.name),
      names,
    );
    assert.equal(registered[0].settings.attributes.formId.default, 0);
    assert.equal(registered[0].settings.attributes.show.default, "form");
    assert.equal(registered[0].settings.supports.html, false);
    assert.equal(registered[1].settings.save(), null);

    loaded.restore();
  });
});
