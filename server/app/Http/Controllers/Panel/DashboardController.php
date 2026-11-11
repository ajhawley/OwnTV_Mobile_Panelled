<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\IptvServer;
use App\Models\MaintenanceMessage;
use App\Models\PanelMetadata;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('panel.dashboard', [
            'serverCount' => IptvServer::query()->count(),
            'enabledServerCount' => IptvServer::query()->where('enabled', true)->count(),
            'messageCount' => MaintenanceMessage::query()->where('enabled', true)->count(),
            'metadata' => PanelMetadata::current(),
        ]);
    }
}
