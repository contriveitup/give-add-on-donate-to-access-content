(function (root) {
  function translate(text) {
    var wp = root.wp;

    if (wp && wp.i18n && typeof wp.i18n.__ === "function") {
      return wp.i18n.__(text, "dtac-give");
    }

    return text;
  }

  function normalizeFormId(value) {
    var id = parseInt(value, 10);

    return id > 0 ? id : 0;
  }

  function normalizeShow(value) {
    return value === "message" ? "message" : "form";
  }

  function getFormOptions(localized) {
    var source = localized;

    if (!source && typeof dtacGiveBlocks !== "undefined") {
      source = dtacGiveBlocks;
    }

    if (source && source.forms && source.forms.length) {
      return source.forms;
    }

    return [
      {
        label: translate("Use default form"),
        value: "0",
      },
    ];
  }

  function registerBlocks(wp) {
    if (!wp || !wp.blocks || !wp.element) {
      return [];
    }

    var el = wp.element.createElement;
    var Fragment = wp.element.Fragment;
    var registerBlockType = wp.blocks.registerBlockType;
    var blockEditor = wp.blockEditor || wp.editor || {};
    var InnerBlocks = blockEditor.InnerBlocks;
    var InspectorControls = blockEditor.InspectorControls;
    var useInnerBlocksProps = blockEditor.useInnerBlocksProps;
    var PanelBody = wp.components && wp.components.PanelBody;
    var SelectControl = wp.components && wp.components.SelectControl;
    var __ = translate;

    function fallbackBlockProps(extra) {
      extra = extra || {};
      extra.className = extra.className || "";
      extra.style = extra.style || { minHeight: "48px" };
      return extra;
    }
    fallbackBlockProps.save = fallbackBlockProps;

    var useBlockProps = blockEditor.useBlockProps || fallbackBlockProps;

    function innerBlocksSettings() {
      var settings = {
        templateLock: false,
        template: [
          [
            "core/paragraph",
            {
              placeholder: __(
                "Add the content guests must donate to see.",
                "dtac-give",
              ),
            },
          ],
        ],
      };

      if (InnerBlocks && InnerBlocks.ButtonBlockAppender) {
        settings.renderAppender = InnerBlocks.ButtonBlockAppender;
      }

      return settings;
    }

    function donationGateControls(props) {
      if (!InspectorControls || !PanelBody || !SelectControl) {
        return null;
      }

      return el(
        InspectorControls,
        {},
        el(
          PanelBody,
          { title: __("Donation gate", "dtac-give"), initialOpen: true },
          el(SelectControl, {
            label: __("Give donation form", "dtac-give"),
            help: __(
              "Guests see this GiveWP form. The selected form ID is stored for unlocking.",
              "dtac-give",
            ),
            value: String(props.attributes.formId || 0),
            options: getFormOptions(),
            onChange: function (value) {
              props.setAttributes({ formId: normalizeFormId(value) });
            },
          }),
          el(SelectControl, {
            label: __("Show for guests", "dtac-give"),
            value: normalizeShow(props.attributes.show),
            options: [
              { label: __("Donation form", "dtac-give"), value: "form" },
              {
                label: __("Restriction message", "dtac-give"),
                value: "message",
              },
            ],
            onChange: function (value) {
              props.setAttributes({ show: normalizeShow(value) });
            },
          }),
        ),
      );
    }

    registerBlockType("dtac/restricted-content", {
      apiVersion: 2,
      title: __("Restricted Content", "dtac-give"),
      description: __(
        "Hide inner content until a qualifying donation is made.",
        "dtac-give",
      ),
      category: "widgets",
      icon: "lock",
      supports: {
        html: false,
      },
      attributes: {
        formId: {
          type: "number",
          default: 0,
        },
        show: {
          type: "string",
          default: "form",
        },
      },
      edit: function (props) {
        var blockProps = useBlockProps({
          className: "dtac-restricted-content-editor",
        });
        var innerSettings = innerBlocksSettings();
        var innerProps = useInnerBlocksProps
          ? useInnerBlocksProps(blockProps, innerSettings)
          : blockProps;
        var innerContent = useInnerBlocksProps
          ? el("div", innerProps)
          : el(
              "div",
              blockProps,
              InnerBlocks
                ? el(InnerBlocks, innerSettings)
                : el(
                    "p",
                    {},
                    __(
                      "Inner blocks are unavailable in this editor.",
                      "dtac-give",
                    ),
                  ),
            );

        return el(Fragment, {}, donationGateControls(props), innerContent);
      },
      save: function () {
        if (!InnerBlocks || !InnerBlocks.Content) {
          return null;
        }

        return el(InnerBlocks.Content);
      },
    });

    registerBlockType("dtac/my-unlocked-content", {
      apiVersion: 2,
      title: __("My Unlocked Content", "dtac-give"),
      description: __(
        "List content the current donor has unlocked.",
        "dtac-give",
      ),
      category: "widgets",
      icon: "list-view",
      edit: function () {
        return el(
          "p",
          useBlockProps(),
          __(
            "This block lists content the current donor has unlocked.",
            "dtac-give",
          ),
        );
      },
      save: function () {
        return null;
      },
    });

    return ["dtac/restricted-content", "dtac/my-unlocked-content"];
  }

  var isModule = typeof module !== "undefined" && module.exports;
  var registered = isModule ? [] : registerBlocks(root.wp);

  var api = {
    normalizeFormId: normalizeFormId,
    normalizeShow: normalizeShow,
    getFormOptions: getFormOptions,
    registerBlocks: registerBlocks,
    registered: registered,
  };

  root.dtacGiveBlocksUi = api;

  if (typeof module !== "undefined" && module.exports) {
    module.exports = api;
  }
})(typeof window !== "undefined" ? window : globalThis);
