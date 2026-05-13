<?php

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Schema;
use ribeiroconde\Dictionary\Livewire\DictionaryWizard;
use ribeiroconde\Dictionary\Tests\TestCase;

uses(TestCase::class);

it('shows a warning instead of throwing when dictionary migrations are missing', function () {
    Schema::drop('dictionary_blueprint_revisions');
    Schema::drop('dictionary_blueprints');

    $component = app(DictionaryWizard::class);
    $component->openDictionary();

    Notification::assertNotified('Dictionary migrations are missing');

    expect(data_get($component->mountedActions, '0.name'))->toBeNull();
});
