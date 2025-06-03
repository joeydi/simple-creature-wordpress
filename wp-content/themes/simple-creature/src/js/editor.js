wp.domReady(() => {
  wp.blocks.unregisterBlockStyle("core/image", ["rounded"]);
  wp.blocks.unregisterBlockStyle("core/separator", ["wide", "dots"]);
});

wp.hooks.addFilter("wpBootstrapBlocks.row.horizontalGuttersOptions", "dl", () => {
  return [
    {
      label: "None",
      value: "gx-0",
    },
    {
      label: "Small",
      value: "gx-12",
    },
    {
      label: "Medium",
      value: "gx-24",
    },
    {
      label: "Large",
      value: "gx-36",
    },
  ];
});

wp.hooks.addFilter("wpBootstrapBlocks.row.verticalGuttersOptions", "dl", () => {
  return [
    {
      label: "None",
      value: "gy-0",
    },
    {
      label: "Small",
      value: "gy-12",
    },
    {
      label: "Medium",
      value: "gy-24",
    },
    {
      label: "Large",
      value: "gy-36",
    },
  ];
});
