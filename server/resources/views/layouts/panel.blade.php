<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ config('app.name') }}</title>
    <style>
        :root{font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color:#182230;background:#f4f6f9;font-synthesis:none}
        *{box-sizing:border-box}body{margin:0}a{color:#1959a6;text-decoration:none}a:hover{text-decoration:underline}
        .top{background:#111c2c;color:#fff;padding:16px max(22px,calc((100% - 1120px)/2));display:flex;align-items:center;justify-content:space-between;gap:20px}
        .brand{font-weight:750;letter-spacing:.2px}.nav{display:flex;align-items:center;gap:18px;flex-wrap:wrap}.nav a{color:#dbe5f1}
        .wrap{max-width:1120px;margin:30px auto;padding:0 22px}.pagehead{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:22px}
        h1{font-size:26px;margin:0 0 6px}h2{font-size:18px;margin:0 0 14px}.muted{color:#64748b;font-size:13px}
        .card{background:#fff;border:1px solid #e1e7ef;border-radius:13px;padding:22px;margin-bottom:18px;box-shadow:0 2px 8px #14233b08}
        .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:14px}.metric{font-size:28px;font-weight:750;margin-top:6px}
        label{display:block;font-size:13px;font-weight:650;margin:13px 0 6px}input,textarea,select{width:100%;padding:11px 12px;border:1px solid #cbd5e1;border-radius:8px;font:inherit;background:#fff}textarea{min-height:110px;resize:vertical}
        .btn{display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:8px;background:#1959a6;color:#fff;font-weight:650;padding:10px 15px;font:inherit;cursor:pointer}.btn:hover{background:#12477f;text-decoration:none}.btn.secondary{background:#e9eff7;color:#20364f}.btn.danger{background:#a62b35}.actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-top:18px}
        table{width:100%;border-collapse:collapse}th,td{text-align:left;padding:12px 10px;border-bottom:1px solid #e9edf3;vertical-align:top}th{font-size:12px;color:#64748b;text-transform:uppercase;letter-spacing:.04em}
        .pill{display:inline-block;font-size:12px;border-radius:999px;padding:4px 9px;background:#e9eff7;color:#334155}.pill.ok{background:#e5f5ec;color:#17633b}.pill.off{background:#fce9e9;color:#9b2424}
        .alert{padding:12px 14px;background:#e9f5ec;border:1px solid #bfe5ca;color:#205c34;border-radius:9px;margin-bottom:16px}.errors{padding:12px 14px;background:#fff0f0;border:1px solid #f0c5c5;color:#912b2b;border-radius:9px;margin-bottom:16px}
        .inline{display:inline}.checkrow{display:flex;gap:9px;align-items:center;margin-top:14px}.checkrow input{width:auto}.checkrow label{margin:0;font-weight:500}
        .footer{color:#7b8796;font-size:12px;margin:34px 0 12px}.mono{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:12px;overflow-wrap:anywhere}
        @media(max-width:680px){.top{align-items:flex-start;flex-direction:column}.pagehead{align-items:flex-start;flex-direction:column}table{display:block;overflow-x:auto}}
    </style>
</head>
<body>
<header class="top">
    <div class="brand">{{ config('app.name') }}</div>
    @auth
        <nav class="nav">
            <a href="{{ route('panel.dashboard') }}">Dashboard</a>
            <a href="{{ route('panel.servers.index') }}">Servers</a>
            <a href="{{ route('panel.maintenance.index') }}">Maintenance</a>
            <form class="inline" method="POST" action="{{ route('panel.logout') }}">@csrf<button class="btn secondary" type="submit">Sign out</button></form>
        </nav>
    @endauth
</header>
<main class="wrap">
    @if (session('status')) <div class="alert">{{ session('status') }}</div> @endif
    @if ($errors->any())
        <div class="errors"><strong>Please correct the following:</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @yield('content')
    <div class="footer">OwnTV Custom · Server configuration only. Xtream credentials are entered and stored on each user's device.</div>
</main>
</body>
</html>
