<?php

namespace ribeiroconde\Dictionary\Generators;

use Illuminate\Support\Facades\File;
use ribeiroconde\Dictionary\Enums\GenerationMode;
use ribeiroconde\Dictionary\Support\FactoryUpdater;
use ribeiroconde\Dictionary\Support\GenerationPathResolver;
use ribeiroconde\Dictionary\ValueObjects\BlueprintData;

readonly class FactoryGenerator extends AbstractGenerator
{
    protected function getContent(BlueprintData $blueprint): string
    {
        $stub = $this->getStub('factory');

        return $this->replacePlaceholders($stub, [
            '{{ namespace }}' => config('dictionary.factories_namespace', 'Database\\Factories'),
            '{{ model_namespace }}' => GenerationPathResolver::modelsNamespace(),
            '{{ model_class }}' => $blueprint->modelName,
            '{{ factory_class }}' => "{$blueprint->modelName}Factory",
            '{{ factory_definitions }}' => $blueprint->getFactoryDefinitions(),
        ]);
    }

    public function generate(BlueprintData $blueprint): string
    {
        if (! $blueprint->generateFactory) {
            return '';
        }

        $factoryName = "{$blueprint->modelName}Factory";
        $path = GenerationPathResolver::factory($factoryName);

        $this->ensureDirectoryExists($path);

        if (File::exists($path)) {
            if ($blueprint->generationMode === GenerationMode::Create) {
                return $path;
            }

            if ($blueprint->generationMode->shouldMergeExistingArtifacts()) {
                $updatedContent = app(FactoryUpdater::class)->merge(File::get($path), $this->getContent($blueprint));
                $this->writeFormattedFile($path, $updatedContent);

                return $path;
            }
        }

        $this->writeFormattedFile($path, $this->getContent($blueprint));

        return $path;
    }
}
