<?php

namespace ribeiroconde\Dictionary\Contracts;

use ribeiroconde\Dictionary\ValueObjects\BlueprintData;

interface BlueprintImporter
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function import(array $context = []): BlueprintData;
}
