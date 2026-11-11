<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\IptvServer;
use App\Models\MaintenanceMessage;
use App\Models\PanelMetadata;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MaintenanceMessageController extends Controller
{
    public function index(): View
    {
        return view('panel.maintenance.index', [
            'messages' => MaintenanceMessage::query()->with('server')->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('panel.maintenance.form', [
            'message' => new MaintenanceMessage(),
            'servers' => IptvServer::query()->orderBy('name')->get(),
            'editing' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        DB::transaction(function () use ($data): void {
            MaintenanceMessage::query()->create($data);
            PanelMetadata::touchConfiguration();
        });

        return redirect()->route('panel.maintenance.index')->with('status', 'Maintenance message created.');
    }

    public function edit(MaintenanceMessage $message): View
    {
        return view('panel.maintenance.form', [
            'message' => $message,
            'servers' => IptvServer::query()->orderBy('name')->get(),
            'editing' => true,
        ]);
    }

    public function update(Request $request, MaintenanceMessage $message): RedirectResponse
    {
        $data = $this->validated($request);
        DB::transaction(function () use ($message, $data): void {
            $message->update($data);
            PanelMetadata::touchConfiguration();
        });

        return redirect()->route('panel.maintenance.index')->with('status', 'Maintenance message updated.');
    }

    public function destroy(MaintenanceMessage $message): RedirectResponse
    {
        DB::transaction(function () use ($message): void {
            $message->delete();
            PanelMetadata::touchConfiguration();
        });

        return redirect()->route('panel.maintenance.index')->with('status', 'Maintenance message deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:2000'],
            'severity' => ['required', Rule::in(['info', 'warning', 'urgent'])],
            'server_id' => ['nullable', 'integer', 'exists:iptv_servers,id'],
            'enabled' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
        ]) + ['enabled' => $request->boolean('enabled')];
    }
}
