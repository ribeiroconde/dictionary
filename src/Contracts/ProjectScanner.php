<?php

namespace Lartisan\Dictionary\Contracts;

interface ProjectScanner
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function scan(): array;
}
