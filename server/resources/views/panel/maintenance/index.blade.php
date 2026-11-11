@extends('layouts.panel')
@section('title', 'Maintenance messages')
@section('content')
<div class="pagehead"><div><h1>Maintenance messages</h1><div class="muted">General notices appear to everyone. Server-specific notices appear when that server is selected.</div></div><a class="btn" href="{{ route('panel.maintenance.create') }}">Create message</a></div>
<div class="card">
    @if ($messages->isEmpty())
        <p class="muted">No maintenance messages have been created.</p>
    @else
    <table><thead><tr><th>Message</th><th>Target</th><th>Schedule</th><th>Status</th><th>Actions</th></tr></thead><tbody>
    @foreach ($messages as $message)
    <tr>
        <td><strong>{{ $message->title }}</strong><div class="muted">{{ $message->body }}</div><span class="pill">{{ ucfirst($message->severity) }}</span></td>
        <td>{{ $message->server?->name ?? 'All users' }}</td>
        <td class="muted">From: {{ $message->starts_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') ?? 'Immediately' }}<br>Until: {{ $message->ends_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') ?? 'No end date' }}</td>
        <td><span class="pill {{ $message->enabled ? 'ok' : 'off' }}">{{ $message->enabled ? 'Enabled' : 'Disabled' }}</span></td>
        <td><div class="actions" style="margin:0"><a class="btn secondary" href="{{ route('panel.maintenance.edit', $message) }}">Edit</a>
            <form method="POST" action="{{ route('panel.maintenance.destroy', $message) }}" onsubmit="return confirm('Delete this maintenance message?');">@csrf @method('DELETE')<button class="btn danger" type="submit">Delete</button></form></div></td>
    </tr>
    @endforeach
    </tbody></table>
    @endif
</div>
@endsection
