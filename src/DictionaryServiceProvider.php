<?php

namespace ribeiroconde\Dictionary;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Support\Facades\Blade;
use ribeiroconde\Dictionary\Commands\InstallCommand;
use ribeiroconde\Dictionary\Commands\UpgradeCommand;
use ribeiroconde\Dictionary\Contracts\DictionaryCapabilityResolver;
use ribeiroconde\Dictionary\Livewire\DictionaryTrigger;
use ribeiroconde\Dictionary\Livewire\DictionaryWizard;
use ribeiroconde\Dictionary\Livewire\BlueprintsTable;
use ribeiroconde\Dictionary\Support\DictionaryBlockRegistry;
use ribeiroconde\Dictionary\Support\DictionaryCapabilityRegistry;
use ribeiroconde\Dictionary\Support\DictionaryUiExtensionRegistry;
use ribeiroconde\Dictionary\Support\BlueprintGenerationHookRegistry;
use ribeiroconde\Dictionary\View\Components\CodePreview;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class DictionaryServiceProvider extends PackageServiceProvider
{
    public static string $name = 'dictionary';

    public static string $viewNamespace = 'dictionary';

    public function configurePackage(Package $package): void
    {
        $package
            ->name('dictionary')
            ->hasConfigFile('dictionary')
            ->hasViews('dictionary')
            ->hasMigration('create_dictionary_blueprints_table')
            ->hasMigration('create_dictionary_blueprint_revisions_table')
            ->hasMigration('update_dictionary_blueprint_revisions_table_add_snapshot_metadata')
            ->hasAssets()
            ->hasCommands(InstallCommand::class, UpgradeCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(DictionaryCapabilityRegistry::class);
        $this->app->singleton(DictionaryCapabilityResolver::class, fn ($app): DictionaryCapabilityResolver => $app->make(DictionaryCapabilityRegistry::class));
        $this->app->singleton(DictionaryBlockRegistry::class);
        $this->app->singleton(DictionaryUiExtensionRegistry::class);
        $this->app->singleton(BlueprintGenerationHookRegistry::class);
    }

    public function packageBooted(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'dictionary');

        Livewire::component('dictionary-trigger', DictionaryTrigger::class);
        Livewire::component('dictionary-wizard', DictionaryWizard::class);
        Livewire::component('blueprints-table', BlueprintsTable::class);

        if (class_exists(FilamentAsset::class)) {
            FilamentAsset::register(
                $this->getAssets(),
                $this->getAssetPackageName()
            );
        }

        if (class_exists(FilamentIcon::class)) {
            FilamentIcon::register($this->getIcons());
        }

        Blade::component('dictionary-code-preview', CodePreview::class);
    }

    protected function getAssets(): array
    {
        return [
            Css::make('dictionary', __DIR__.'/../resources/dist/dictionary.css'),
            Css::make('prism-tomorrow', __DIR__.'/../resources/dist/prism-tomorrow.min.css'),
            Js::make('prism-core', __DIR__.'/../resources/dist/prism.min.js'),
            Js::make('prism-markup-templating', __DIR__.'/../resources/dist/prism-markup-templating.min.js'),
            Js::make('prism-php', __DIR__.'/../resources/dist/prism-php.min.js'),
            Js::make('prism-init', __DIR__.'/../resources/dist/prism-php.min.js')->html(<<<'JS'
                <script data-navigate-track>
                    (function () {
                        function highlightAll() {
                            if (typeof Prism !== 'undefined' && Prism.languages.php) {
                                Prism.highlightAll();
                            }
                        }

                        document.addEventListener('livewire:init', function () {
                            Livewire.hook('commit', ({ succeed }) => {
                                succeed(() => queueMicrotask(highlightAll));
                            });
                        });
                    })();
                </script>
            JS),
        ];
    }

    protected function getAssetPackageName(): ?string
    {
        return 'ribeiroconde/filament-dictionary';
    }

    protected function getIcons(): array
    {
        return [];
    }

    protected function getCommands(): array
    {
        return [
            InstallCommand::class,
            UpgradeCommand::class,
        ];
    }

    protected function getMigrations(): array
    {
        return [];
    }
}
