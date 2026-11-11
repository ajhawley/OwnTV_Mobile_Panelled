<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\IptvServer;
use App\Models\PanelMetadata;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class IptvServerController extends Controller
{
    public function index(): View
    {
        return view('panel.servers.index', [
            'servers' => IptvServer::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('panel.servers.form', ['server' => new IptvServer(), 'editing' => false]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        DB::transaction(function () use ($data): void {
            IptvServer::query()->create($data);
            PanelMetadata::touchConfiguration();
        });

        return redirect()->route('panel.servers.index')->with('status', 'Server added.');
    }

    public function edit(IptvServer $server): View
    {
        return view('panel.servers.form', ['server' => $server, 'editing' => true]);
    }

    public function update(Request $request, IptvServer $server): RedirectResponse
    {
        $data = $this->validated($request);
        DB::transaction(function () use ($server, $data): void {
            $server->update($data);
            PanelMetadata::touchConfiguration();
        });

        return redirect()->route('panel.servers.index')->with('status', 'Server updated.');
    }

    public function destroy(IptvServer $server): RedirectResponse
    {
        DB::transaction(function () use ($server): void {
            $server->delete();
            PanelMetadata::touchConfiguration();
        });

        return redirect()->route('panel.servers.index')->with('status', 'Server deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'base_url' => ['required', 'url', 'max:2048', 'regex:/^https?:\/\//i'],
            'enabled' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ]);

        $data['base_url'] = rtrim(trim($data['base_url']), '/');
        $data['enabled'] = $request->boolean('enabled');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        if (!Str::startsWith(Str::lower($data['base_url']), ['http://', 'https://'])) {
            throw ValidationException::withMessages(['base_url' => 'The provider address must begin with http:// or https://.']);
        }

        return $data;
    }
}
