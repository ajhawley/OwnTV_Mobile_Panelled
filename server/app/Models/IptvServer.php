<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class IptvServer extends Model
{
    protected $fillable = ['name', 'base_url', 'enabled', 'sort_order'];

    protected function casts(): array
    {
        return [
            // Laravel encrypts this value in SQLite and decrypts it when read by the app.
            'base_url' => 'encrypted',
            'enabled' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (IptvServer $server): void {
            $server->uuid ??= (string) Str::uuid();
        });
    }

    public function maintenanceMessages()
    {
        return $this->hasMany(MaintenanceMessage::class, 'server_id');
    }
}
