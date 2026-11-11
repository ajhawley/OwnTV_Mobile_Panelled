@extends('layouts.panel')
@section('title', 'IPTV servers')
@section('content')
<div class="pagehead"><div><h1>IPTV servers</h1><div class="muted">The app receives the full list on launch. Edit an existing entry to change its domain without changing its stable ID.</div></div><a class="btn" href="{{ route('panel.servers.create') }}">Add server</a></div>
<div class="card">
    @if ($servers->isEmpty())
        <p class="muted">No servers have been added yet.</p>
    @else
    <table><thead><tr><th>Name</th><th>Provider URL</th><th>Status</th><th>Order</th><th>Actions</th></tr></thead><tbody>
    @foreach ($servers as $server)
    <tr>
        <td><strong>{{ $server->name }}</strong><div class="muted mono">{{ $server->uuid }}</div></td>
        <td class="mono">{{ $server->base_url }}</td>
        <td><span class="pill {{ $server->enabled ? 'ok' : 'off' }}">{{ $server->enabled ? 'Enabled' : 'Disabled' }}</span></td>
        <td>{{ $server->sort_order }}</td>
        <td><div class="actions" style="margin:0"><a class="btn secondary" href="{{ route('panel.servers.edit', $server) }}">Edit</a>
            <form method="POST" action="{{ route('panel.servers.destroy', $server) }}" onsubmit="return confirm('Delete this server? Its targeted maintenance messages will also be deleted.');">@csrf @method('DELETE')<button class="btn danger" type="submit">Delete</button></form></div></td>
    </tr>
    @endforeach
    </tbody></table>
    @endif
</div>
@endsection
