<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IptvServer;
use App\Models\MaintenanceMessage;
use App\Models\PanelMetadata;
use Illuminate\Http\JsonResponse;

class BootstrapController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $metadata = PanelMetadata::current();
        $now = now();

        // Include disabled servers so the app can explicitly disable/hide them locally.
        $servers = IptvServer::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (IptvServer $server): array => [
                'id' => $server->uuid,
                'name' => $server->name,
                'base_url' => $server->base_url,
                'enabled' => (bool) $server->enabled,
                'sort_order' => (int) $server->sort_order,
                'updated_at' => $server->updated_at?->utc()->toIso8601String(),
            ])
            ->values()
            ->all();

        $messages = MaintenanceMessage::query()
            ->with('server')
            ->where('enabled', true)
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
            ->orderBy('id')
            ->get()
            ->map(fn (MaintenanceMessage $message): array => [
                'id' => $message->uuid,
                'title' => $message->title,
                'body' => $message->body,
                'type' => $message->severity,
                'server_id' => $message->server?->uuid,
                'starts_at' => $message->starts_at?->utc()->toIso8601String(),
                'ends_at' => $message->ends_at?->utc()->toIso8601String(),
            ])
            ->values()
            ->all();

        $payload = [
            'installation_id' => $metadata->installation_id,
            'config_updated_at' => $metadata->config_updated_at->utc()->toIso8601String(),
            'servers' => $servers,
            'maintenance_messages' => $messages,
        ];

        $checksum = hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        return response()->json($payload + ['checksum' => $checksum])
            ->withHeaders([
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'X-Content-Type-Options' => 'nosniff',
            ]);
    }
}
