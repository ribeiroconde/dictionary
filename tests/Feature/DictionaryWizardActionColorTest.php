<?php

use Lartisan\Dictionary\DictionaryPlugin;
use Lartisan\Dictionary\Livewire\DictionaryWizard;
use Lartisan\Dictionary\Tests\TestCase;

uses(TestCase::class);

it('keeps the action color unset by default so filament uses the primary color', function () {
    $component = app(DictionaryWizard::class);
    $action = $component->openDictionaryAction();

    expect($action->getColor())->toBeNull();
});

it('can apply a custom string action color', function () {
    $component = app(DictionaryWizard::class);
    $component->actionColor = 'success';

    $action = $component->openDictionaryAction();

    expect($action->getColor())->toBe('success');
});

it('can apply an array-based action color', function () {
    $component = app(DictionaryWizard::class);
    $component->actionColor = [
        500 => '#6366f1',
        600 => '#4f46e5',
    ];

    $action = $component->openDictionaryAction();

    expect($action->getColor())->toBe([
        500 => '#6366f1',
        600 => '#4f46e5',
    ]);
});

it('stores the configured action color on the plugin', function () {
    $plugin = DictionaryPlugin::make()->actionColor('warning');

    expect($plugin->getActionColor())->toBe('warning');
});
