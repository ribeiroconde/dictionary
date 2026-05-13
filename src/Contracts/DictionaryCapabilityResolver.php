<?php

namespace ribeiroconde\Dictionary\Contracts;

interface DictionaryCapabilityResolver
{
    public function has(string $capability): bool;
}
