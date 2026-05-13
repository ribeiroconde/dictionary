<?php

namespace ribeiroconde\Dictionary\Livewire;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use ribeiroconde\Dictionary\Actions\DictionaryAction;
use ribeiroconde\Dictionary\Models\Blueprint as DictionaryBlueprint;
use ribeiroconde\Dictionary\Support\DictionaryMigrationStatus;
use ribeiroconde\Dictionary\Support\BlueprintDeletionService;
use Livewire\Attributes\On;
use Livewire\Component;

class DictionaryWizard extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public bool $isIconButton = false;

    public string|array|null $actionColor = null;

    public function openDictionaryAction(): Action
    {
        $action = DictionaryAction::make();

        if ($this->actionColor !== null) {
            $action->color($this->actionColor);
        }

        if ($this->isIconButton) {
            $action->iconButton()
                ->tooltip(__('Open Filament Dictionary'));
        } else {
            $action->extraAttributes([
                'class' => 'w-full justify-start',
            ]);
        }

        return $action;
    }

    public function render()
    {
        return view('dictionary::dictionary-wizard');
    }

    #[On('open-dictionary-wizard')]
    public function openDictionary(): void
    {
        if (filled($this->mountedActions)) {
            return;
        }

        if (! $this->ensureMigrationsAreReady()) {
            return;
        }

        $this->mountAction('openDictionary');
    }

    #[On('load-blueprint')]
    public function loadBlueprint(int $id): void
    {
        if (! $this->ensureMigrationsAreReady()) {
            return;
        }

        $blueprint = DictionaryBlueprint::find($id);

        if ($blueprint) {
            $data = $blueprint->toFormData();

            $this->openDictionary();
            $this->getMountedActionSchema()->fill($data);

            Notification::make()
                ->title(__('Data was loaded!'))
                ->success()
                ->send();
        }
    }

    #[On('load-blueprint-data')]
    public function loadBlueprintData(array $data): void
    {
        if (! $this->ensureMigrationsAreReady()) {
            return;
        }

        $this->openDictionary();
        $this->getMountedActionSchema()->fill($data);

        Notification::make()
            ->title(__('Blueprint revision loaded!'))
            ->success()
            ->send();
    }

    public function deleteBlueprint(int $id): void
    {
        if (! $this->ensureMigrationsAreReady()) {
            return;
        }

        $blueprint = DictionaryBlueprint::find($id);

        if ($blueprint === null) {
            return;
        }

        app(BlueprintDeletionService::class)->deleteSnapshotOnly($blueprint);

        Notification::make()
            ->title('Blueprint deleted!')
            ->success()
            ->send();
    }

    protected function ensureMigrationsAreReady(): bool
    {
        if (app(DictionaryMigrationStatus::class)->isReady()) {
            return true;
        }

        $this->notifyMissingMigrations();

        return false;
    }

    protected function notifyMissingMigrations(): void
    {
        Notification::make()
            ->title(__('Dictionary migrations are missing'))
            ->body(__('Run `php artisan dictionary:upgrade` to publish migrations, migrate, and backfill revisions in one step.'))
            ->warning()
            ->persistent()
            ->send();
    }
}
