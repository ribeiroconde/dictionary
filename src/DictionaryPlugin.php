<?php

namespace ribeiroconde\Dictionary;

use Composer\InstalledVersions;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use ribeiroconde\Dictionary\Support\DictionaryBlockRegistry;
use ribeiroconde\Dictionary\Support\DictionaryCapabilityRegistry;
use ribeiroconde\Dictionary\Support\DictionaryUiExtensionRegistry;
use ribeiroconde\Dictionary\Support\BlueprintGenerationHookRegistry;

class DictionaryPlugin implements Plugin
{
    protected string $renderHook = PanelsRenderHook::GLOBAL_SEARCH_BEFORE;

    protected bool $hasCustomRenderHook = false;

    protected array $availableRenderHooks = [
        PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
        PanelsRenderHook::GLOBAL_SEARCH_AFTER,
        PanelsRenderHook::USER_MENU_AFTER,
        PanelsRenderHook::SIDEBAR_NAV_START,
        PanelsRenderHook::SIDEBAR_NAV_END,
        PanelsRenderHook::SIDEBAR_FOOTER,
    ];

    protected bool $isIconButton = false;

    protected string|array|null $actionColor = null;

    protected bool $showProBanner = false;

    public function getId(): string
    {
        return 'dictionary';
    }

    public function renderHook(string $hook): static
    {
        if ($this->isAllowedRenderHool($hook)) {
            $this->renderHook = $hook;
            $this->hasCustomRenderHook = true;
        }

        return $this;
    }

    protected function isAllowedRenderHool(string $hook): bool
    {
        return in_array($hook, $this->availableRenderHooks);
    }

    public function iconButton(bool $condition = true): static
    {
        $this->isIconButton = $condition;

        return $this;
    }

    public function actionColor(string|array|null $color): static
    {
        $this->actionColor = $color;

        return $this;
    }

    public function showProBanner(bool $condition = true): static
    {
        $this->showProBanner = $condition;

        return $this;
    }

    public function shouldShowProBanner(): bool
    {
        return $this->showProBanner;
    }

    public function getActionColor(): string|array|null
    {
        return $this->actionColor;
    }

    public function register(Panel $panel): void
    {
        $panel
            ->pages([
                // Pages\DictionaryPage::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        if (! config('dictionary.show', ! app()->isProduction())) {
            return;
        }

        if (! $this->hasCustomRenderHook) {
            $this->renderHook = $panel->hasTopbar()
                ? PanelsRenderHook::GLOBAL_SEARCH_BEFORE
                : PanelsRenderHook::SIDEBAR_NAV_END;
        }

        $this->registerDictionaryTrigger();
        $this->registerDictionaryModalHost();
    }

    protected function registerDictionaryTrigger(): void
    {
        FilamentView::registerRenderHook(
            $this->renderHook,
            fn (): string => Blade::render(
                "@livewire('dictionary-trigger', ['isIconButton' => \$isIconButton, 'actionColor' => \$actionColor])",
                [
                    'isIconButton' => $this->isIconButton,
                    'actionColor' => $this->getActionColor(),
                ],
            ),
        );
    }

    protected function registerDictionaryModalHost(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn (): string => Blade::render("@livewire('dictionary-wizard')"),
        );
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public static function capabilities(): DictionaryCapabilityRegistry
    {
        return app(DictionaryCapabilityRegistry::class);
    }

    public static function blocks(): DictionaryBlockRegistry
    {
        return app(DictionaryBlockRegistry::class);
    }

    public static function uiExtensions(): DictionaryUiExtensionRegistry
    {
        return app(DictionaryUiExtensionRegistry::class);
    }

    public static function generationHooks(): BlueprintGenerationHookRegistry
    {
        return app(BlueprintGenerationHookRegistry::class);
    }

    public static function version(): string
    {
        return InstalledVersions::getPrettyVersion('ribeiroconde/filament-dictionary') ?? 'dev';
    }
}
