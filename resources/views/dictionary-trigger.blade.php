<div class="flex w-full">
    @if ($isIconButton)
        <x-filament::icon-button :color="$this->getTriggerColor()" :icon="$this->getTriggerIcon()" :label="__('Open Filament Dictionary')" :tooltip="__('Open Filament Dictionary')"
            wire:click="openDictionary" />
    @else
        <x-filament::button :color="$this->getTriggerColor()" :icon="$this->getTriggerIcon()" :badge="$this->isProInstalled() ? 'BETA' : null" :badge-color="'danger'"
            class="w-full justify-start" wire:click="openDictionary">
            {{ $this->isProInstalled() ? __('DictionaryPRO') : __('Dictionary') }}
        </x-filament::button>
    @endif
</div>
