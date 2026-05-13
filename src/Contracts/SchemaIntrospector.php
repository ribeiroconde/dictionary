<?php

namespace ribeiroconde\Dictionary\Contracts;

interface SchemaIntrospector
{
    /**
     * @return array<string, mixed>
     */
    public function describeTable(string $table): array;
}
