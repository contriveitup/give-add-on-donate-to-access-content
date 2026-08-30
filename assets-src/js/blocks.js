(function (wp) {
  if (!wp || !wp.blocks || !wp.element) {
    return;
  }

  var el = wp.element.createElement;
  var registerBlockType = wp.blocks.registerBlockType;
  var blockEditor = wp.blockEditor || wp.editor || {};
  var InnerBlocks = blockEditor.InnerBlocks;
  var InspectorControls = blockEditor.InspectorControls;
  var PanelBody = wp.components.PanelBody;
  var SelectControl = wp.components.SelectControl;
  var __ = wp.i18n.__;

  function getFormOptions() {
    if (
      typeof dtacGiveBlocks !== "undefined" &&
      dtacGiveBlocks.forms &&
      dtacGiveBlocks.forms.length
    ) {
      return dtacGiveBlocks.forms;
    }

    return [
      {
        label: __("Use default form", "dtac-give"),
        value: "0",
      },
    ];
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
      return el(
        wp.element.Fragment,
        {},
        el(
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
                props.setAttributes({ formId: parseInt(value, 10) || 0 });
              },
            }),
            el(SelectControl, {
              label: __("Show for guests", "dtac-give"),
              value: props.attributes.show || "form",
              options: [
                { label: __("Donation form", "dtac-give"), value: "form" },
                {
                  label: __("Restriction message", "dtac-give"),
                  value: "message",
                },
              ],
              onChange: function (value) {
                props.setAttributes({ show: value });
              },
            }),
          ),
        ),
        el(
          "div",
          { className: "dtac-restricted-content-editor" },
          el(
            "p",
            { className: "dtac-restricted-content-editor__label" },
            __(
              "Restricted content — add blocks that stay hidden until a donation is made.",
              "dtac-give",
            ),
          ),
          el(InnerBlocks),
          re,
        ),
      );
    },
    save: function () {
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
        {},
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
})(window.wp);
