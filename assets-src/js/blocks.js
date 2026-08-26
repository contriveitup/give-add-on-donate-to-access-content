(function (wp) {
  if (!wp || !wp.blocks || !wp.element) {
    return;
  }

  var el = wp.element.createElement;
  var registerBlockType = wp.blocks.registerBlockType;
  var InnerBlocks = wp.blockEditor
    ? wp.blockEditor.InnerBlocks
    : wp.editor.InnerBlocks;
  var InspectorControls = wp.blockEditor
    ? wp.blockEditor.InspectorControls
    : wp.editor.InspectorControls;
  var PanelBody = wp.components.PanelBody;
  var TextControl = wp.components.TextControl;
  var SelectControl = wp.components.SelectControl;
  var __ = wp.i18n.__;

  registerBlockType("dtac/restricted-content", {
    apiVersion: 2,
    title: __("Restricted Content", "dtac-give"),
    description: __(
      "Hide inner content until a qualifying donation is made.",
      "dtac-give",
    ),
    category: "widgets",
    icon: "lock",
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
            el(TextControl, {
              label: __("Give form ID", "dtac-give"),
              type: "number",
              value: props.attributes.formId || 0,
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
        el(InnerBlocks),
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
