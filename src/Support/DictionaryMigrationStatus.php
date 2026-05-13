<?php

namespace Lartisan\Dictionary\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DictionaryMigrationStatus
{
    /**
     * @return array<int, string>
     */
    public function missingTables(): array
    {
        return array_values(array_filter([
            'dictionary_blueprints',
            'dictionary_blueprint_revisions',
        ], fn (string $table): bool => ! $this->hasTable($table)));
    }

    public function isReady(): bool
    {
        return $this->missingTables() === [];
    }

    public function hasStoredRevisions(): bool
    {
        if (! $this->isReady()) {
            return false;
        }

        try {
            return DB::table('dictionary_blueprint_revisions')->exists();
        } catch (Throwable) {
            return false;
        }
    }

    protected function hasTable(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (Throwable) {
            return false;
        }
    }
}
