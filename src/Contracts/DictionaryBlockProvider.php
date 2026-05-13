<?php

namespace ribeiroconde\Dictionary\Contracts;

interface DictionaryBlockProvider
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function blocks(): array;
}
