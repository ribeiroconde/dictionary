<?php

use Lartisan\Dictionary\Livewire\DictionaryTrigger;
use Lartisan\Dictionary\Tests\TestCase;

uses(TestCase::class);

it('reports pro is not installed when the pro service provider class does not exist', function () {
    $trigger = app(DictionaryTrigger::class);

    expect($trigger->isProInstalled())->toBeFalse();
});

it('badge expression evaluates to null when pro is not installed', function () {
    $trigger = app(DictionaryTrigger::class);

    $badge = $trigger->isProInstalled() ? 'BETA' : null;

    expect($badge)->toBeNull();
});
