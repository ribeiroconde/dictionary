<?php

use Filament\Actions\Action;
use Filament\Schemas\Components\Tabs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Lartisan\Dictionary\DictionaryPlugin;
use Lartisan\Dictionary\Contracts\DictionaryBlockProvider;
use Lartisan\Dictionary\Support\DictionaryBlockRegistry;
use Lartisan\Dictionary\Support\DictionaryCapabilityRegistry;
use Lartisan\Dictionary\Support\DictionaryUiExtensionRegistry;
use Lartisan\Dictionary\Support\BlueprintGenerationHookRegistry;
use Lartisan\Dictionary\Support\BlueprintGenerationService;
use Lartisan\Dictionary\Support\GenerationPathResolver;
use Lartisan\Dictionary\Tests\TestCase;
use Lartisan\Dictionary\ValueObjects\BlueprintData;

uses(TestCase::class);

beforeEach(function () {
    app(DictionaryCapabilityRegistry::class)->flush();
    app(DictionaryBlockRegistry::class)->flush();
    app(DictionaryUiExtensionRegistry::class)->flush();
    app(BlueprintGenerationHookRegistry::class)->flush();
});

afterEach(function () {
    app(DictionaryCapabilityRegistry::class)->flush();
    app(DictionaryBlockRegistry::class)->flush();
    app(DictionaryUiExtensionRegistry::class)->flush();
    app(BlueprintGenerationHookRegistry::class)->flush();

    File::delete(GenerationPathResolver::model('ExtensionPost'));
    File::delete(GenerationPathResolver::factory('ExtensionPostFactory'));
    File::delete(GenerationPathResolver::seeder('ExtensionPostSeeder'));
    File::delete(GenerationPathResolver::resource('ExtensionPostResource'));

    $resourceDirectory = GenerationPathResolver::resourceDirectory('ExtensionPostResource');

    if (File::isDirectory($resourceDirectory)) {
        File::deleteDirectory($resourceDirectory);
    }

    foreach (File::glob(database_path('migrations/*_extension_posts_table.php')) as $migrationFile) {
        File::delete($migrationFile);
    }

    DB::table('migrations')
        ->where('migration', 'like', '%_extension_posts_table')
        ->delete();

    if (Schema::hasTable('extension_posts')) {
        Schema::drop('extension_posts');
    }
});

it('resolves dictionary capabilities through the shared registry', function () {
    $registry = app(DictionaryCapabilityRegistry::class);

    expect($registry->has('premium.blocks'))->toBeFalse();

    $registry->define('premium.blocks', true)
        ->define('premium.revisions.browser', fn (): bool => true);

    expect(DictionaryPlugin::capabilities()->has('premium.blocks'))->toBeTrue()
        ->and(DictionaryPlugin::capabilities()->has('premium.revisions.browser'))->toBeTrue()
        ->and($registry->all())->toMatchArray([
            'premium.blocks' => true,
            'premium.revisions.browser' => true,
        ]);
});

it('merges registered dictionary blocks without duplicating existing types', function () {
    $registry = app(DictionaryBlockRegistry::class);

    $registry->register([
        'type' => 'premium-metrics',
        'label' => 'Premium Metrics',
    ])->extend(new class implements DictionaryBlockProvider
    {
        public function blocks(): array
        {
            return [[
                'type' => 'premium-carousel',
                'label' => 'Premium Carousel',
            ]];
        }
    });

    $mergedBlocks = DictionaryPlugin::blocks()->merge([
        [
            'type' => 'hero',
            'label' => 'Hero',
        ],
        [
            'type' => 'premium-carousel',
            'label' => 'Premium Carousel from Base',
        ],
    ]);

    expect(array_column($mergedBlocks, 'type'))
        ->toBe(['hero', 'premium-carousel', 'premium-metrics']);
});

it('stores ui extensions for create, existing resources, extra tabs, and record actions', function () {
    $registry = app(DictionaryUiExtensionRegistry::class);

    $registry->registerCreateEditExtension(fn (): array => ['create-edit-fragment'])
        ->registerExistingResourcesExtension(fn (): array => ['existing-resource-fragment'])
        ->registerTab(fn (): Tabs\Tab => Tabs\Tab::make('Premium'))
        ->registerBlueprintsTableRecordActions(fn (): Action => Action::make('revision_history'));

    expect(DictionaryPlugin::uiExtensions()->createEditExtensions())->toBe(['create-edit-fragment'])
        ->and(DictionaryPlugin::uiExtensions()->existingResourcesExtensions())->toBe(['existing-resource-fragment'])
        ->and(DictionaryPlugin::uiExtensions()->blueprintsTableRecordActions())->toHaveCount(1)
        ->and(DictionaryPlugin::uiExtensions()->blueprintsTableRecordActions()[0])->toBeInstanceOf(Action::class)
        ->and(DictionaryPlugin::uiExtensions()->tabs())->toHaveCount(1)
        ->and(DictionaryPlugin::uiExtensions()->tabs()[0])->toBeInstanceOf(Tabs\Tab::class);
});

it('runs registered post-generation hooks after a blueprint is generated', function () {
    $hookCalls = [];

    DictionaryPlugin::generationHooks()->afterGenerate(function ($blueprint, BlueprintData $blueprintData, $plan, bool $shouldRunMigration) use (&$hookCalls): void {
        $hookCalls[] = [
            'blueprint_id' => $blueprint->id,
            'table_name' => $blueprintData->tableName,
            'should_run_migration' => $shouldRunMigration,
            'plan_has_operations' => count($plan->schemaOperations) >= 0,
        ];
    });

    $blueprintData = BlueprintData::fromArray([
        'table_name' => 'extension_posts',
        'model_name' => 'ExtensionPost',
        'columns' => [
            [
                'name' => 'title',
                'type' => 'string',
            ],
        ],
        'gen_factory' => false,
        'gen_seeder' => false,
        'gen_resource' => false,
        'run_migration' => true,
    ]);

    $result = app(BlueprintGenerationService::class)->generate($blueprintData);

    expect($result['shouldRunMigration'])->toBeTrue()
        ->and($hookCalls)->toHaveCount(1)
        ->and($hookCalls[0]['table_name'])->toBe('extension_posts')
        ->and($hookCalls[0]['should_run_migration'])->toBeTrue();
});
