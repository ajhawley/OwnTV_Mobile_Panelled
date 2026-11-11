@extends('layouts.panel')
@section('title', $editing ? 'Edit maintenance message' : 'Create maintenance message')
@section('content')
<div class="pagehead"><div><h1>{{ $editing ? 'Edit maintenance message' : 'Create maintenance message' }}</h1><div class="muted">Times use the configured panel timezone: {{ config('app.timezone') }}.</div></div><a class="btn secondary" href="{{ route('panel.maintenance.index') }}">Back to messages</a></div>
<div class="card" style="max-width:760px">
    <form method="POST" action="{{ $editing ? route('panel.maintenance.update', $message) : route('panel.maintenance.store') }}">
        @csrf @if($editing) @method('PUT') @endif
        <label for="title">Title</label><input id="title" name="title" required maxlength="120" value="{{ old('title', $message->title) }}" placeholder="Scheduled maintenance">
        <label for="body">Message</label><textarea id="body" name="body" required maxlength="2000" placeholder="Explain what users may experience...">{{ old('body', $message->body) }}</textarea>
        <label for="severity">Message type</label><select id="severity" name="severity" required>@foreach(['info'=>'Information','warning'=>'Warning','urgent'=>'Urgent'] as $value=>$label)<option value="{{ $value }}" @selected(old('severity', $message->severity ?: 'info') === $value)>{{ $label }}</option>@endforeach</select>
        <label for="server_id">Audience</label><select id="server_id" name="server_id"><option value="">All users (general announcement)</option>@foreach($servers as $server)<option value="{{ $server->id }}" @selected((string)old('server_id', $message->server_id) === (string)$server->id)>{{ $server->name }}</option>@endforeach</select>
        <label for="starts_at">Start time (optional)</label><input id="starts_at" name="starts_at" type="datetime-local" value="{{ old('starts_at', $message->starts_at?->format('Y-m-d\TH:i')) }}">
        <label for="ends_at">End time (optional)</label><input id="ends_at" name="ends_at" type="datetime-local" value="{{ old('ends_at', $message->ends_at?->format('Y-m-d\TH:i')) }}">
        <div class="checkrow"><input id="enabled" name="enabled" type="checkbox" value="1" @checked(old('enabled', $message->exists ? $message->enabled : true))><label for="enabled">Enabled</label></div>
        <div class="actions"><button class="btn" type="submit">{{ $editing ? 'Save message' : 'Create message' }}</button><a class="btn secondary" href="{{ route('panel.maintenance.index') }}">Cancel</a></div>
    </form>
</div>
@endsection
