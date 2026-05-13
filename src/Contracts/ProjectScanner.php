<?php

namespace ribeiroconde\Dictionary\Contracts;

interface ProjectScanner
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function scan(): array;
}
