<?php

declare(strict_types=1);

namespace Modules\Core\Concerns;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Nwidart\Modules\Traits\PathNamespace;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

trait PrimaryServiceProviderFunctions
{
    use PathNamespace;

    public function registerTranslations(): void
    {
        $langPaths = [
            resource_path("lang/modules/{$this->nameLower}"),
            module_path($this->name, 'lang'),
        ];

        foreach ($langPaths as $path) {
            if (is_dir($path)) {
                $this->loadTranslationsFrom($path, $this->nameLower);
                // $this->loadJsonTranslationsFrom($path); // optioneel
            }
        }
    }

    public function registerViews(): void
    {
        $sourcePath = module_path($this->name, 'resources/views');
        $targetPath = resource_path("views/modules/{$this->nameLower}");

        $this->publishes([
            $sourcePath => $targetPath,
        ], ['views', "{$this->nameLower}-module-views"]);

        $this->loadViewsFrom(
            array_merge($this->getPublishableViewPaths(), [$sourcePath]),
            $this->nameLower
        );

        Blade::componentNamespace(
            config('modules.namespace')."\\{$this->name}\\View\\Components",
            $this->nameLower
        );
    }

    protected function merge_config_from(string $path, string $key): void
    {
        $existing = Config::get($key, []);
        $moduleConfig = require $path;

        Config::set($key, array_replace_recursive($existing, $moduleConfig));
    }

    protected function registerConfig(): void
    {
        $configPath = module_path($this->name, config('modules.paths.generator.config.path'));

        if (! is_dir($configPath)) {
            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($configPath)
        );

        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $filePath = $file->getPathname();
            $relative = str_replace($configPath.DIRECTORY_SEPARATOR, '', $filePath);
            $keyRaw = str_replace(['.php', DIRECTORY_SEPARATOR], ['', '.'], $relative);
            $keyRaw = is_array($keyRaw) ? implode('', $keyRaw) : $keyRaw;

            $segments = explode('.', "{$this->nameLower}.{$keyRaw}");
            $normalized = [];

            foreach ($segments as $segment) {
                if (end($normalized) !== $segment) {
                    $normalized[] = $segment;
                }
            }

            $key = ($relative === 'config.php') ? $this->nameLower : implode('.', $normalized);

            $this->publishes([
                $filePath => config_path($relative),
            ], 'config');

            $this->merge_config_from($filePath, $key);
        }
    }

    /**
     * @return array<string>
     */
    private function getPublishableViewPaths(): array
    {
        $paths = [];
        $viewRoots = config('view.paths');

        if (! is_array($viewRoots)) {
            return [];
        }

        foreach ($viewRoots as $basePath) {
            if (! is_string($basePath)) {
                continue;
            }

            $path = "{$basePath}/modules/{$this->nameLower}";
            if (is_dir($path)) {
                $paths[] = $path;
            }
        }

        return $paths;
    }
}
