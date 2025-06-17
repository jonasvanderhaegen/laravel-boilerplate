<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Providers\PrimaryServiceProvider;

/**
 * Helper om protected/private methodes aan te roepen
 */
function invokeMethod(object $object, string $method, array $params = []): mixed
{
    $reflection = new ReflectionClass($object);
    $method = $reflection->getMethod($method);
    $method->setAccessible(true);

    return $method->invokeArgs($object, $params);
}

beforeEach(function () {
    $this->provider = new PrimaryServiceProvider(app());
    $this->nameLower = 'core';
});

afterEach(function () {
    // Verwijder test-bestanden en directories
    File::delete(resource_path('views/modules/core/demo.blade.php'));
    File::deleteDirectory(resource_path('views/modules/core'));

    File::delete(module_path('Core', 'config/test.php'));
    File::deleteDirectory(module_path('Core', 'config'));

    File::delete(module_path('Core', 'resources/views/demo.blade.php'));
    File::deleteDirectory(module_path('Core', 'resources/views'));

    File::delete(module_path('Core', 'lang/en/dummy.php'));
    File::deleteDirectory(module_path('Core', 'lang/en'));
    File::deleteDirectory(module_path('Core', 'lang'));
});

it('returns view paths when view.modules exists', function () {
    config()->set('view.paths', [resource_path('views')]);
    $path = resource_path('views/modules/core');
    File::ensureDirectoryExists($path);

    $paths = invokeMethod($this->provider, 'getPublishableViewPaths');
    expect($paths)->toBe([$path]);
});

it('registers and loads views', function () {
    $viewPath = module_path('Core', 'resources/views');
    File::ensureDirectoryExists($viewPath);
    File::put($viewPath.'/demo.blade.php', 'Hello Sail');

    $this->provider->registerViews();
    $output = View::make('core::demo')->render();

    expect($output)->toContain('Hello Sail');
});

it('merges config file correctly', function () {
    $configPath = module_path('Core', 'config/test.php');
    File::ensureDirectoryExists(dirname($configPath));
    File::put($configPath, '<?php return ["feature" => ["enabled" => true]];');

    invokeMethod($this->provider, 'merge_config_from', [$configPath, 'core.test']);

    expect(Config::get('core.test.feature.enabled'))->toBeTrue();
});

it('registers all config files in directory', function () {
    $configPath = module_path('Core', 'config/test.php');
    File::ensureDirectoryExists(dirname($configPath));
    File::put($configPath, '<?php return ["feature" => ["flag" => "on"]];');

    invokeMethod($this->provider, 'registerConfig');

    expect(Config::get('core.test.feature.flag'))->toBe('on');
});

it('returns empty array when view.paths is not an array', function () {
    config(['view.paths' => null]);

    $anonProvider = new class(app()) extends ServiceProvider
    {
        use Modules\Core\Concerns\PrimaryServiceProviderFunctions;

        public string $nameLower = 'core';
    };

    $paths = invokeMethod($anonProvider, 'getPublishableViewPaths');
    expect($paths)->toBeArray()->toBeEmpty();
});

it('loads translations from available paths', function () {
    $langPath = module_path('Core', 'lang/en');
    File::ensureDirectoryExists($langPath);
    File::put($langPath.'/dummy.php', '<?php return ["hello" => "world"];');

    $this->provider->registerTranslations();

    expect(trans('core::dummy.hello'))->toBe('world');
});

it('ignores non-string values in view.paths when getting publishable view paths', function () {
    config(['view.paths' => [
        resource_path('views'),
        123,                 // int
        null,                // null
        ['nested' => true],  // array
        new stdClass(),      // object
    ]]);

    // Alleen de eerste (valide) string wordt gebruikt
    $expectedPath = resource_path('views/modules/core');
    File::ensureDirectoryExists($expectedPath);

    $paths = invokeMethod($this->provider, 'getPublishableViewPaths');

    expect($paths)->toBe([$expectedPath]);
});
