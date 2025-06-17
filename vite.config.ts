import tailwindcss from "@tailwindcss/vite";
import laravel from "laravel-vite-plugin";
import { defineConfig } from "vite";
import collectModuleAssetsPaths from "./vite-module-loader";

async function getConfig() {
    const paths = ["resources/css/app.css", "resources/ts/app.ts"];
    const allPaths = await collectModuleAssetsPaths(paths, "Modules");

    return defineConfig({
        resolve: {
            extensions: [".js", ".ts"], // allow .ts imports
            alias: {
                "@": "/resources/js",
                Modules: "/Modules",
            },
        },
        plugins: [
            tailwindcss(),
            laravel({
                input: allPaths,
                refresh: true,
            }),
        ],
    });
}

export default getConfig();
