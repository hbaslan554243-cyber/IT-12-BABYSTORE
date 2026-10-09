<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') · Aizen Baby Store</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --clr-bg: #EAF6FC;
            --clr-card: #FFFFFF;
            --clr-primary: #8FD3F4;
            --clr-primary-dark: #6EC1E4;
            --clr-primary-darker: #55B0D6;
            --clr-powder: #CDEBF7;
            --clr-sidebar: #F3FAFD;
            --clr-text: #33475B;
            --clr-text-muted: #83A0B4;
            --clr-border: #DFF0F8;
            --clr-warning-soft: #FFF1C2;
            --clr-warning-text: #9A7B1E;
            --clr-success-soft: #DCF6E7;
            --clr-success-text: #2C8558;
            --clr-danger-soft: #FBE1E1;
            --clr-danger-text: #B15757;
            --shadow-sm: 0 2px 10px rgba(120, 170, 210, 0.10);
            --shadow-md: 0 10px 28px rgba(120, 170, 210, 0.14);
            --radius: 16px;
        }
        * { box-sizing: border-box; }
        body {
            background: var(--clr-bg);
            color: var(--clr-text);
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
        }

        /* ---------- Sidebar ---------- */
        .sidebar {
            position: fixed;
            top: 0; left: 0; bottom: 0;
            width: 250px;
            background: var(--clr-sidebar);
            border-right: 1px solid var(--clr-border);
            padding: 24px 16px;
            display: flex;
            flex-direction: column;
            z-index: 1000;
            transition: transform .25s ease;
        }
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 4px 10px 24px 10px;
        }
        .sidebar-brand .logo-bubble {
            width: 40px; height: 40px;
            background: linear-gradient(160deg, var(--clr-primary), var(--clr-primary-dark));
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
            box-shadow: var(--shadow-sm);
        }
        .sidebar-brand .brand-text { line-height: 1.1; }
        .sidebar-brand .brand-text strong { display: block; font-size: 15px; color: var(--clr-text); }
        .sidebar-brand .brand-text span { font-size: 11px; color: var(--clr-text-muted); }

        .sidebar-section-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--clr-text-muted);
            margin: 18px 10px 6px 10px;
            font-weight: 700;
        }
        .sidebar-nav { list-style: none; padding: 0; margin: 0; }
        .sidebar-nav li { margin-bottom: 2px; }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 12px;
            color: var(--clr-text);
            text-decoration: none;
            font-weight: 600;
            font-size: 14.5px;
            transition: background .15s ease, color .15s ease;
        }
        .sidebar-nav a svg { flex-shrink: 0; opacity: .75; }
        .sidebar-nav a:hover { background: var(--clr-powder); }
        .sidebar-nav a.active {
            background: linear-gradient(135deg, var(--clr-primary), var(--clr-primary-dark));
            color: #fff;
            box-shadow: var(--shadow-sm);
        }
        .sidebar-nav a.active svg { opacity: 1; }

        .sidebar-footer {
            margin-top: auto;
            padding-top: 14px;
            border-top: 1px solid var(--clr-border);
        }
        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 12px;
        }
        .sidebar-user .avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: var(--clr-powder);
            display: flex; align-items: center; justify-content: center;
            font-weight: 800;
            color: var(--clr-primary-darker);
            font-size: 14px;
        }
        .sidebar-user .meta { line-height: 1.1; overflow: hidden; }
        .sidebar-user .meta strong { display: block; font-size: 13.5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 130px; }
        .sidebar-user .meta span { font-size: 11px; color: var(--clr-text-muted); text-transform: capitalize; }

        /* ---------- Main area ---------- */
        .main-area { margin-left: 250px; min-height: 100vh; display: flex; flex-direction: column; }
        .topbar {
            display: flex; align-items: center; justify-content: space-between;
            padding: 18px 28px;
            background: transparent;
        }
        .topbar h1 { font-size: 20px; font-weight: 800; margin: 0; color: var(--clr-text); }
        .sidebar-toggle { display: none; }

        .content-wrap { padding: 0 28px 40px 28px; flex: 1; }

        /* ---------- Cards / components (retheme Bootstrap defaults) ---------- */
        .card {
            background: var(--clr-card);
            border: 1px solid var(--clr-border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
        }
        .stat-card { border-left: 5px solid var(--clr-primary); border-radius: var(--radius); }

        .btn { border-radius: 12px; font-weight: 700; font-size: 14.5px; padding: 9px 18px; }
        .btn-brand, .btn-danger {
            background: linear-gradient(135deg, var(--clr-primary), var(--clr-primary-dark)) !important;
            border: none !important;
            color: #fff !important;
            box-shadow: var(--shadow-sm);
        }
        .btn-brand:hover, .btn-danger:hover { filter: brightness(0.97); color: #fff !important; }
        .btn-outline-danger {
            color: var(--clr-danger-text) !important;
            border-color: #F0C9C9 !important;
            background: #fff !important;
        }
        .btn-outline-danger:hover { background: var(--clr-danger-soft) !important; }
        .btn-outline-primary {
            color: var(--clr-primary-darker) !important;
            border-color: var(--clr-primary) !important;
        }
        .btn-outline-primary:hover { background: var(--clr-powder) !important; color: var(--clr-text) !important; }
        .pay-method-btn.active {
            background: linear-gradient(135deg, var(--clr-primary), var(--clr-primary-dark)) !important;
            color: #fff !important;
            border-color: transparent !important;
        }
        .btn-outline-secondary { border-radius: 12px; }
        .btn-success {
            background: linear-gradient(135deg, #8FE0B4, #63C98D) !important;
            border: none !important;
        }

        .table { background: var(--clr-card); border-radius: var(--radius); overflow: hidden; }
        .table thead { background: var(--clr-sidebar); }
        .table th { color: var(--clr-text-muted); font-size: 12.5px; text-transform: uppercase; letter-spacing: .04em; font-weight: 800; border-bottom: 1px solid var(--clr-border) !important; }
        .table td { border-color: var(--clr-border) !important; vertical-align: middle; font-size: 14.5px; }
        .table-bordered { border: 1px solid var(--clr-border); }

        .badge { font-weight: 700; border-radius: 8px; padding: 5px 10px; }
        .badge.bg-warning { background: var(--clr-warning-soft) !important; color: var(--clr-warning-text) !important; }
        .badge.bg-success { background: var(--clr-success-soft) !important; color: var(--clr-success-text) !important; }
        .badge.bg-secondary { background: #EAEFF3 !important; color: var(--clr-text-muted) !important; }

        .low-stock { background-color: #FFF7E0 !important; }
        .out-of-stock-row { background-color: #FBE9E9 !important; }
        .badge.bg-danger-soft { background: var(--clr-danger-soft) !important; color: var(--clr-danger-text) !important; }

        .form-control, .form-select {
            width: 100%;
            display: block;
            border-radius: 12px;
            border-color: var(--clr-border);
            padding: 9px 14px;
            font-family: inherit;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--clr-primary);
            box-shadow: 0 0 0 .2rem rgba(143, 211, 244, .25);
        }
        .form-label { font-weight: 700; font-size: 13.5px; color: var(--clr-text); }

        .alert { border-radius: var(--radius); border: none; }
        .alert-success { background: var(--clr-success-soft); color: var(--clr-success-text); }
        .alert-danger { background: var(--clr-danger-soft); color: var(--clr-danger-text); }

        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); box-shadow: var(--shadow-md); }
            .main-area { margin-left: 0; }
            .sidebar-toggle { display: inline-flex; }
        }
    </style>
</head>
<body>

@php
    $user = auth()->user();
    $isOwner = $user && $user->isOwner();
@endphp

@auth
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="logo-bubble">🍼</div>
        <div class="brand-text">
            <strong>Aizen Baby Store</strong>
            <span>Management System</span>
        </div>
    </div>

    <div class="sidebar-section-label">Main</div>
    <ul class="sidebar-nav">
        <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M9 21v-6h6v6"/></svg>
            Dashboard
        </a></li>
    </ul>

    <div class="sidebar-section-label">Operations</div>
    <ul class="sidebar-nav">
        <li><a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/><path d="m20.7 7-8.7-5-8.7 5 8.7 5 8.7-5Z"/><path d="M4 7v10l8 5 8-5V7"/></svg>
            Inventory
        </a></li>
        <li><a href="{{ route('sales.create') }}" class="{{ request()->routeIs('sales.create') ? 'active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.5 3h2l2.7 12.4a2 2 0 0 0 2 1.6h8.6a2 2 0 0 0 2-1.6L21.5 8H6"/></svg>
            New Sale (POS)
        </a></li>
        <li><a href="{{ route('sales.log') }}" class="{{ request()->routeIs('sales.log') || request()->routeIs('sales.show') ? 'active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M3 12h18M3 18h18"/><circle cx="3.5" cy="6" r=".5" fill="currentColor"/><circle cx="3.5" cy="12" r=".5" fill="currentColor"/><circle cx="3.5" cy="18" r=".5" fill="currentColor"/></svg>
            Sales Records
        </a></li>
        <li><a href="{{ route('purchase-orders.index') }}" class="{{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 3h15v13H1z"/><path d="M16 8h4l3 3v5h-7V8Z"/><circle cx="5.5" cy="18.5" r="1.5"/><circle cx="18.5" cy="18.5" r="1.5"/></svg>
            Purchase Orders
        </a></li>
        @if($isOwner)
        <li><a href="{{ route('suppliers.index') }}" class="{{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Suppliers
        </a></li>
        @endif
    </ul>

    @if($isOwner)
    <div class="sidebar-section-label">Reports</div>
    <ul class="sidebar-nav">
        <li><a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M18 17V9M13 17V5M8 17v-4"/></svg>
            Revenue Reports
        </a></li>
    </ul>
    @endif

    @if($isOwner)
    <div class="sidebar-section-label">Administration</div>
    <ul class="sidebar-nav">
        <li><a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7"/></svg>
            Manage Users
        </a></li>
    </ul>
    @endif

    <div class="sidebar-footer">
        <a href="{{ route('profile.edit') }}" class="sidebar-user text-decoration-none" style="{{ request()->routeIs('profile.edit') ? 'background:var(--clr-powder);' : '' }}">
            <div class="avatar">{{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}</div>
            <div class="meta">
                <strong>{{ $user->name }}</strong>
                <span>{{ $user->role }}</span>
            </div>
        </a>
        <form action="{{ route('logout') }}" method="POST" class="mt-2">
            @csrf
            <button class="btn btn-outline-secondary btn-sm w-100">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                Logout
            </button>
        </form>
    </div>
</aside>
@endauth

<div class="main-area">
    @auth
    <div class="topbar">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary sidebar-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
            <h1>@yield('title', 'Dashboard')</h1>
        </div>
        <div class="text-muted small d-none d-md-block">{{ now()->format('l, F j, Y') }}</div>
    </div>
    @endauth

    <div class="content-wrap">
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
@yield('scripts')
</body>
</html>
