<?php

namespace Lartisan\Dictionary\Contracts;

use Lartisan\Dictionary\ValueObjects\BlueprintData;

interface BlueprintImporter
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function import(array $context = []): BlueprintData;
}
