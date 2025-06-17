import fs from 'fs/promises';
import { dirname, join } from 'path';
import { pathToFileURL, fileURLToPath } from 'url';

// “Polyfill” __filename and __dirname in ESM:
const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

async function collectModuleAssetsPaths(paths: string[], modulesPath: string) {
    modulesPath = join(__dirname, modulesPath);

    const moduleStatusesPath = join(__dirname, 'modules_statuses.json');

    try {
        // Read module_statuses.json
        const moduleStatusesContent = await fs.readFile(moduleStatusesPath, 'utf-8');
        const moduleStatuses = JSON.parse(moduleStatusesContent);

        // Read module directories
        const moduleDirectories = await fs.readdir(modulesPath);

        for (const moduleDir of moduleDirectories) {
            if (moduleDir === '.DS_Store') {
                // Skip .DS_Store directory
                continue;
            }

            // Check if the module is enabled (status is true)
            if (moduleStatuses[moduleDir] === true) {
                const viteConfigPath = join(modulesPath, moduleDir, 'vite.config.js');

                try {
                    await fs.access(viteConfigPath);
                    // Convert to a file URL for Windows compatibility
                    const moduleConfigURL = pathToFileURL(viteConfigPath);

                    // Import the module-specific Vite configuration
                    const moduleConfig = await import(moduleConfigURL.href);

                    if (moduleConfig.paths && Array.isArray(moduleConfig.paths)) {
                        paths.push(...moduleConfig.paths);
                    }
                } catch (error) {
                    // vite.config.js does not exist, skip this module
                }
            }
        }
    } catch (error) {
        console.error(`Error reading module statuses or module configurations: ${error}`);
    }

    return paths;
}

export default collectModuleAssetsPaths;
