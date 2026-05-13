<?php

use Filament\Actions\Action;
use Lartisan\Dictionary\Actions\DictionaryAction;
use Lartisan\Dictionary\DictionaryPlugin;
use Lartisan\Dictionary\Livewire\DictionaryWizard;
use Lartisan\Dictionary\Tests\TestCase;

uses(TestCase::class);

it('places the version badge in the modal footer actions', function () {
    $livewire = app(DictionaryWizard::class);
    $action = DictionaryAction::make()->livewire($livewire);

    $footerActions = collect($action->getModalFooterActions())
        ->keyBy(fn (Action $a) => $a->getName());

    expect($footerActions)
        ->toHaveKey('cancel')
        ->toHaveKey('dictionary_version_badge');

    expect($footerActions->get('dictionary_version_badge')->getLabel())
        ->toContain('Dictionary version')
        ->toContain(DictionaryPlugin::version());
});

it('applies the dictionary-modal class to the modal window for footer alignment CSS', function () {
    $livewire = app(DictionaryWizard::class);
    $action = DictionaryAction::make()->livewire($livewire);

    $windowAttributes = $action->getExtraModalWindowAttributes();

    expect($windowAttributes['class'])->toContain('dictionary-modal');
});
