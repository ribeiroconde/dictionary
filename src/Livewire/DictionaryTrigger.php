<?php

namespace Lartisan\Dictionary\Livewire;

use Filament\Support\Icons\Heroicon;
use Lartisan\DictionaryPro\DictionaryProServiceProvider;
use Livewire\Component;

class DictionaryTrigger extends Component
{
    public bool $isIconButton = false;

    public string|array|null $actionColor = null;

    public function openDictionary(): void
    {
        $this->dispatch('open-dictionary-wizard');
    }

    public function getTriggerColor(): string|array
    {
        return $this->actionColor ?? 'primary';
    }

    public function getTriggerIcon(): Heroicon
    {
        return Heroicon::Square3Stack3d;
    }

    public function isProInstalled(): bool
    {
        return class_exists(DictionaryProServiceProvider::class);
    }

    public function render()
    {
        return view('dictionary::dictionary-trigger');
    }
}
