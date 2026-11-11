@extends('layouts.panel')
@section('title', $editing ? 'Edit server' : 'Add server')
@section('content')
<div class="pagehead"><div><h1>{{ $editing ? 'Edit IPTV server' : 'Add IPTV server' }}</h1><div class="muted">Use the full provider base URL, including protocol and optional port.</div></div><a class="btn secondary" href="{{ route('panel.servers.index') }}">Back to servers</a></div>
<div class="card" style="max-width:720px">
    <form method="POST" action="{{ $editing ? route('panel.servers.update', $server) : route('panel.servers.store') }}">
        @csrf @if($editing) @method('PUT') @endif
        <label for="name">Display name</label><input id="name" name="name" required maxlength="120" value="{{ old('name', $server->name) }}" placeholder="Provider One">
        <label for="base_url">Provider base URL</label><input id="base_url" name="base_url" type="url" required maxlength="2048" value="{{ old('base_url', $server->base_url) }}" placeholder="https://provider.example:8080">
        <div class="muted">Include http:// or https:// and the port when the provider requires one. Prefer HTTPS. HTTP providers expose login credentials in transit.</div>
        <label for="sort_order">Display order</label><input id="sort_order" name="sort_order" type="number" min="0" max="100000" value="{{ old('sort_order', $server->sort_order ?? 0) }}">
        <div class="checkrow"><input id="enabled" name="enabled" type="checkbox" value="1" @checked(old('enabled', $server->exists ? $server->enabled : true))><label for="enabled">Enabled and available in the app</label></div>
        @if($editing)<p class="muted">Stable server ID: <span class="mono">{{ $server->uuid }}</span>. Keep editing this record when the provider changes its domain; do not delete and recreate it.</p>@endif
        <div class="actions"><button class="btn" type="submit">{{ $editing ? 'Save changes' : 'Add server' }}</button><a class="btn secondary" href="{{ route('panel.servers.index') }}">Cancel</a></div>
    </form>
</div>
@endsection
