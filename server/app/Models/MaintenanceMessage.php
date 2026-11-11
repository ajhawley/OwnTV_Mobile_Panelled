<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MaintenanceMessage extends Model
{
    protected $fillable = ['title', 'body', 'severity', 'server_id', 'enabled', 'starts_at', 'ends_at'];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (MaintenanceMessage $message): void {
            $message->uuid ??= (string) Str::uuid();
        });
    }

    public function server()
    {
        return $this->belongsTo(IptvServer::class, 'server_id');
    }
}
