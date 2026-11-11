<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PanelMetadata extends Model
{
    protected $table = 'panel_metadata';

    protected $fillable = ['installation_id', 'config_updated_at'];

    protected function casts(): array
    {
        return ['config_updated_at' => 'immutable_datetime'];
    }

    public static function current(): self
    {
        // Singleton id=1. If the DB is freshly created, this generates a new installation UUID.
        return static::query()->firstOrCreate(
            ['id' => 1],
            ['installation_id' => (string) Str::uuid(), 'config_updated_at' => now()],
        );
    }

    public static function touchConfiguration(): self
    {
        $metadata = static::current();
        $metadata->forceFill(['config_updated_at' => now()])->save();
        return $metadata->refresh();
    }
}
