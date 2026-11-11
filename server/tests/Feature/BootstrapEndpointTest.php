<?php

namespace Tests\Feature;

use App\Models\IptvServer;
use App\Models\MaintenanceMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BootstrapEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_bootstrap_returns_installation_identity_servers_and_checksum(): void
    {
        $server = IptvServer::query()->create([
            'name' => 'Provider One',
            'base_url' => 'https://provider.example:8080',
            'enabled' => true,
            'sort_order' => 5,
        ]);

        $response = $this->getJson('/panel/api/v1/bootstrap');
        $response->assertOk()
            ->assertJsonPath('servers.0.id', $server->uuid)
            ->assertJsonPath('servers.0.base_url', 'https://provider.example:8080')
            ->assertJsonPath('servers.0.enabled', true)
            ->assertJsonStructure(['installation_id', 'config_updated_at', 'checksum', 'servers', 'maintenance_messages']);

        $this->assertSame(64, strlen((string) $response->json('checksum')));
        $this->assertNotSame('https://provider.example:8080', $server->getRawOriginal('base_url'));
    }

    public function test_bootstrap_includes_current_global_maintenance_notice(): void
    {
        MaintenanceMessage::query()->create([
            'title' => 'Maintenance',
            'body' => 'Some services may be unavailable.',
            'severity' => 'warning',
            'enabled' => true,
            'starts_at' => now()->subMinute(),
            'ends_at' => now()->addHour(),
        ]);

        $this->getJson('/panel/api/v1/bootstrap')
            ->assertOk()
            ->assertJsonPath('maintenance_messages.0.title', 'Maintenance')
            ->assertJsonPath('maintenance_messages.0.type', 'warning')
            ->assertJsonPath('maintenance_messages.0.server_id', null);
    }
}
