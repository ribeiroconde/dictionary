<?php

use Filament\Schemas\Components\Tabs as TabsComponent;
use Filament\Schemas\Components\Wizard as WizardComponent;
use Filament\Schemas\Components\Wizard\Step as WizardStep;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use ribeiroconde\Dictionary\Actions\DictionaryAction;
use ribeiroconde\Dictionary\Livewire\DictionaryWizard;
use ribeiroconde\Dictionary\Models\Blueprint as DictionaryBlueprint;
use ribeiroconde\Dictionary\Support\DictionaryUiExtensionRegistry;
use ribeiroconde\Dictionary\Tests\TestCase;
use ribeiroconde\Dictionary\ValueObjects\BlueprintData;

uses(TestCase::class);

beforeEach(function () {
    app(DictionaryUiExtensionRegistry::class)->flush();
});

afterEach(function () {
    app(DictionaryUiExtensionRegistry::class)->flush();

    DB::table('dictionary_blueprint_revisions')->delete();
    DB::table('dictionary_blueprints')->delete();
});

it('keeps only the create edit tab visible when there are no blueprint revisions', function () {
    $livewire = app(DictionaryWizard::class);
    $action = DictionaryAction::make();
    $schema = $action->getSchema(Schema::make($livewire));

    expect($schema)->not->toBeNull();

    $tabs = collect($schema->getComponents())
        ->first(fn ($component) => $component instanceof TabsComponent);

    expect($tabs)->toBeInstanceOf(TabsComponent::class);

    /** @var TabsComponent $tabs */
    $tabComponents = array_values($tabs->getChildSchema()->getComponents());

    expect($tabComponents[0]->getKey(isAbsolute: false))->toBe('dictionary-create-edit-tab')
        ->and($tabComponents)->toHaveCount(1)
        ->and($tabs->getExtraAttributes()['x-on:activate-first-tab.window'] ?? null)->toBe("\$data.tab = 'dictionary-create-edit-tab';");
});

it('shows blueprints first when blueprint revisions exist', function () {
    createBlueprintRevisionFixture();

    $livewire = app(DictionaryWizard::class);
    $action = DictionaryAction::make();
    $schema = $action->getSchema(Schema::make($livewire));

    $tabs = collect($schema->getComponents())
        ->first(fn ($component) => $component instanceof TabsComponent);

    expect($tabs)->toBeInstanceOf(TabsComponent::class);

    /** @var TabsComponent $tabs */
    $tabComponents = array_values($tabs->getChildSchema()->getComponents());

    expect($tabComponents[0]->getKey(isAbsolute: false))->toBe('dictionary-existing-resources-tab')
        ->and($tabComponents[1]->getKey(isAbsolute: false))->toBe('dictionary-create-edit-tab')
        ->and($tabs->getExtraAttributes()['x-on:activate-first-tab.window'] ?? null)->toBe("\$data.tab = 'dictionary-create-edit-tab';");
});

it('keeps the review step limited to generated file previews', function () {
    createBlueprintRevisionFixture();

    $livewire = app(DictionaryWizard::class);
    $action = DictionaryAction::make();
    $schema = $action->getSchema(Schema::make($livewire));

    $tabs = collect($schema->getComponents())
        ->first(fn ($component) => $component instanceof TabsComponent);

    expect($tabs)->toBeInstanceOf(TabsComponent::class);

    /** @var TabsComponent $tabs */
    $createEditTab = collect($tabs->getChildSchema()->getComponents())
        ->first(fn ($component) => $component->getKey(isAbsolute: false) === 'dictionary-create-edit-tab');

    expect($createEditTab)->not->toBeNull();

    /** @var object $createEditTab */
    $wizard = collect($createEditTab->getChildSchema()->getComponents())
        ->first(fn ($component) => $component instanceof WizardComponent);

    expect($wizard)->toBeInstanceOf(WizardComponent::class);

    /** @var WizardComponent $wizard */
    $steps = collect($wizard->getChildSchema()->getComponents())
        ->filter(fn ($component) => $component instanceof WizardStep)
        ->values();

    $eloquentStep = $steps->first(fn (WizardStep $step) => $step->getLabel() === 'Eloquent');
    $reviewStep = $steps->first(fn (WizardStep $step) => $step->getLabel() === 'Review');

    expect($eloquentStep)->toBeInstanceOf(WizardStep::class)
        ->and($reviewStep)->toBeInstanceOf(WizardStep::class)
        ->and($reviewStep->getDescription())->toBe('Preview the generated files');

    $eloquentNames = collect(flattenSchemaComponents($eloquentStep))
        ->map(fn ($component) => method_exists($component, 'getName') ? $component->getName() : null)
        ->filter()
        ->values()
        ->all();

    $reviewComponents = array_values($reviewStep->getChildSchema()->getComponents());
    $reviewNames = collect(flattenSchemaComponents($reviewStep))
        ->map(fn ($component) => method_exists($component, 'getName') ? $component->getName() : null)
        ->filter()
        ->values()
        ->all();

    expect($eloquentNames)->toContain('run_migration', 'allow_likely_renames', 'allow_destructive_changes')
        ->and($reviewComponents)->toHaveCount(1)
        ->and($reviewComponents[0])->toBeInstanceOf(TabsComponent::class)
        ->and($reviewNames)->not->toContain('regeneration_plan', 'run_migration', 'allow_likely_renames', 'allow_destructive_changes');
});

function flattenSchemaComponents(object $component): array
{
    $components = [$component];

    if (! method_exists($component, 'getChildSchema')) {
        return $components;
    }

    foreach ($component->getChildSchema()->getComponents(withHidden: true) as $childComponent) {
        $components = [...$components, ...flattenSchemaComponents($childComponent)];
    }

    return $components;
}

function createBlueprintRevisionFixture(): void
{
    $blueprint = DictionaryBlueprint::create([
        'table_name' => 'dictionary_action_tabs_projects',
        'model_name' => 'DictionaryActionTabsProject',
        'primary_key_type' => 'id',
        'columns' => [
            ['name' => 'title', 'type' => 'string'],
        ],
        'soft_deletes' => false,
        'meta' => [
            'gen_factory' => true,
            'gen_seeder' => true,
            'gen_resource' => true,
            'generation_mode' => 'merge',
            'allow_destructive_changes' => false,
            'allow_likely_renames' => false,
        ],
    ]);

    $blueprint->recordRevision(BlueprintData::fromArray([
        'table_name' => 'dictionary_action_tabs_projects',
        'model_name' => 'DictionaryActionTabsProject',
        'generation_mode' => 'merge',
        'columns' => [
            ['name' => 'title', 'type' => 'string'],
        ],
    ]));
}
