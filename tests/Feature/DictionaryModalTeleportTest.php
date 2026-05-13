<?php

use Lartisan\Dictionary\Tests\TestCase;

uses(TestCase::class);

it('renders the dictionary trigger separately from the body-end modal host', function () {
    $triggerView = file_get_contents(__DIR__.'/../../resources/views/dictionary-trigger.blade.php');
    $hostView = file_get_contents(__DIR__.'/../../resources/views/dictionary-wizard.blade.php');

    expect($triggerView)
        ->toContain('wire:click="openDictionary"')
        ->and($hostView)
        ->toContain('<x-filament-actions::modals />');
});

it('keeps the blueprints table using filament action modals inside the wizard host', function () {
    $view = file_get_contents(__DIR__.'/../../resources/views/livewire/blueprints-table.blade.php');

    expect($view)
        ->toContain('<x-filament-actions::modals />');
});
