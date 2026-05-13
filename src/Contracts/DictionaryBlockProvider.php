<?php

namespace Lartisan\Dictionary\Contracts;

interface DictionaryBlockProvider
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function blocks(): array;
}
