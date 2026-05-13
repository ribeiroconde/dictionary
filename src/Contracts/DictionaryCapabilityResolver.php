<?php

namespace Lartisan\Dictionary\Contracts;

interface DictionaryCapabilityResolver
{
    public function has(string $capability): bool;
}
