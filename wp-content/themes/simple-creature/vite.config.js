import path from "path";
import { defineConfig } from "vite";

const ROOT = path.resolve("../../../");
const BASE = __dirname.replace(ROOT, "");

export default defineConfig({
  base: process.env.NODE_ENV === "production" ? `${BASE}/dist/` : BASE,
  build: {
    manifest: true,
    assetsDir: ".",
    outDir: `dist`,
    emptyOutDir: true,
    sourcemap: true,
    rollupOptions: {
      input: [
        "src/js/editor.js",
        "src/js/main.js",
        "src/scss/main.scss",
        "src/scss/login.scss",
        "src/scss/admin.scss",
        "src/scss/editor.scss",
        "src/scss/mce.scss",
      ],
      output: {
        entryFileNames: "[hash].js",
        assetFileNames: "[hash].[ext]",
      },
    },
  },
  plugins: [
    {
      name: "php",
      handleHotUpdate({ file, server }) {
        if (file.endsWith(".php")) {
          server.ws.send({ type: "full-reload" });
        }
      },
    },
  ],
});
