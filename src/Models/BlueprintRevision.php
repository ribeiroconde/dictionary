<?php

namespace Lartisan\Dictionary\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Lartisan\Dictionary\ValueObjects\BlueprintData;
use Lartisan\Dictionary\ValueObjects\BlueprintRevisionSnapshot;

class BlueprintRevision extends Model
{
    protected $table = 'dictionary_blueprint_revisions';

    protected $fillable = ['blueprint_id', 'revision', 'snapshot_version', 'snapshot', 'meta'];

    protected $casts = [
        'snapshot' => 'array',
        'meta' => 'array',
    ];

    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class, 'blueprint_id');
    }

    public function toBlueprintData(): BlueprintData
    {
        return $this->toSnapshot()->toBlueprintData();
    }

    public function toSnapshot(): BlueprintRevisionSnapshot
    {
        return BlueprintRevisionSnapshot::fromRevision($this);
    }
}
