@extends('layouts.panel')
@section('title', 'Dashboard')
@section('content')
<div class="pagehead"><div><h1>Dashboard</h1><div class="muted">Manage the provider list and messages delivered to the Android app.</div></div></div>
<div class="grid">
    <div class="card"><div class="muted">Configured servers</div><div class="metric">{{ $serverCount }}</div></div>
    <div class="card"><div class="muted">Enabled servers</div><div class="metric">{{ $enabledServerCount }}</div></div>
    <div class="card"><div class="muted">Enabled maintenance messages</div><div class="metric">{{ $messageCount }}</div></div>
</div>
<div class="card">
    <h2>Android configuration API</h2>
    <p class="muted">The app calls this endpoint on each launch. The complete small configuration is returned so clients can detect a changed panel installation or content checksum.</p>
    <p class="mono">{{ url('/panel/api/v1/bootstrap') }}</p>
    <p class="muted">The API never receives friends' Xtream usernames or passwords. Provider URLs are encrypted in SQLite and served to the app over HTTPS.</p>
</div>
<div class="card">
    <h2>Panel installation</h2>
    <p class="muted">Installation ID (keep this stable across normal database restores):</p>
    <div class="mono">{{ $metadata->installation_id }}</div>
    <p class="muted" style="margin-top:14px">Last configuration change: {{ $metadata->config_updated_at->timezone(config('app.timezone'))->format('Y-m-d H:i:s T') }}</p>
</div>
<div class="actions">
    <a class="btn" href="{{ route('panel.servers.create') }}">Add IPTV server</a>
    <a class="btn secondary" href="{{ route('panel.maintenance.create') }}">Create maintenance message</a>
</div>
@endsection
