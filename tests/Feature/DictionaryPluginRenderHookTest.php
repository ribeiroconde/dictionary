<?php

use Filament\Panel;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use ribeiroconde\Dictionary\DictionaryPlugin;
use ribeiroconde\Dictionary\Tests\TestCase;

uses(TestCase::class);

it('defaults to the global search position when the panel topbar is enabled in non-production', function () {
    $plugin = new DictionaryPlugin;

    $plugin->boot(new Panel);

    expect(FilamentView::hasRenderHook(PanelsRenderHook::GLOBAL_SEARCH_BEFORE))->toBeTrue()
        ->and(FilamentView::hasRenderHook(PanelsRenderHook::BODY_END))->toBeTrue()
        ->and(FilamentView::hasRenderHook(PanelsRenderHook::SIDEBAR_NAV_END))->toBeFalse();
});

it('defaults to the sidebar navigation end when the panel topbar is hidden', function () {
    config()->set('dictionary.show', true);

    $plugin = new DictionaryPlugin;

    $plugin->boot((new Panel)->topbar(false));

    expect(FilamentView::hasRenderHook(PanelsRenderHook::SIDEBAR_NAV_END))->toBeTrue()
        ->and(FilamentView::hasRenderHook(PanelsRenderHook::BODY_END))->toBeTrue()
        ->and(FilamentView::hasRenderHook(PanelsRenderHook::GLOBAL_SEARCH_BEFORE))->toBeFalse();
});

it('keeps an explicitly configured render hook when the panel topbar is hidden', function () {
    config()->set('dictionary.show', true);

    $plugin = (new DictionaryPlugin)
        ->renderHook(PanelsRenderHook::GLOBAL_SEARCH_AFTER);

    $plugin->boot((new Panel)->topbar(false));

    expect(FilamentView::hasRenderHook(PanelsRenderHook::GLOBAL_SEARCH_AFTER))->toBeTrue()
        ->and(FilamentView::hasRenderHook(PanelsRenderHook::BODY_END))->toBeTrue()
        ->and(FilamentView::hasRenderHook(PanelsRenderHook::SIDEBAR_NAV_END))->toBeFalse();
});

it('does not register any dictionary hooks when the plugin is explicitly hidden', function () {
    config()->set('dictionary.show', false);

    $plugin = new DictionaryPlugin;

    $plugin->boot(new Panel);

    expect(FilamentView::hasRenderHook(PanelsRenderHook::GLOBAL_SEARCH_BEFORE))->toBeFalse()
        ->and(FilamentView::hasRenderHook(PanelsRenderHook::BODY_END))->toBeFalse()
        ->and(FilamentView::hasRenderHook(PanelsRenderHook::SIDEBAR_NAV_END))->toBeFalse();
});
