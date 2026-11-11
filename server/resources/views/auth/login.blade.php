@extends('layouts.panel')
@section('title', 'Administrator sign in')
@section('content')
<div style="max-width:430px;margin:50px auto">
    <div class="card">
        <h1>Administrator sign in</h1>
        <p class="muted">This panel is for the administrator only. There is no public registration.</p>
        <form method="POST" action="{{ route('panel.login.store') }}">
            @csrf
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
            <div class="checkrow"><input id="remember" name="remember" type="checkbox" value="1"><label for="remember">Remember this device</label></div>
            <div class="actions"><button class="btn" type="submit">Sign in</button></div>
        </form>
    </div>
</div>
@endsection
