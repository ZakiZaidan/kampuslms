<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — KampusLMS</title>
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --success: #16a34a;
            --danger: #dc2626;
            --warning: #d97706;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        a { color: var(--primary); text-decoration: none; }
        a:hover { text-decoration: underline; }
        
        /* Navbar */
        .navbar {
            background: #ffffff;
            border-bottom: 1px solid var(--border);
            padding: 0.75rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .navbar-brand {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        .navbar-nav {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            list-style: none;
        }
        .navbar-nav a {
            color: #334155;
            font-weight: 500;
            font-size: 0.95rem;
        }
        .navbar-nav a:hover {
            color: var(--primary);
            text-decoration: none;
        }
        .badge {
            display: inline-block;
            padding: 0.2rem 0.55rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .badge-danger { background: var(--danger); color: #fff; }
        .badge-success { background: #dcfce7; color: var(--success); }
        .badge-warning { background: #fef3c7; color: var(--warning); }
        .badge-role {
            background: #eff6ff;
            color: var(--primary);
            border: 1px solid #bfdbfe;
            text-transform: uppercase;
        }

        /* Container */
        .container {
            max-width: 1100px;
            margin: 2rem auto;
            padding: 0 1rem;
            width: 100%;
            flex: 1;
        }

        /* Alerts */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }
        .alert-success { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
        .alert-danger { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }

        /* Card */
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }
        .card-header {
            margin-bottom: 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
        }

        /* Buttons */
        .btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            border: none;
            text-align: center;
            text-decoration: none;
        }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-hover); text-decoration: none; color: #fff; }
        .btn-secondary { background: #e2e8f0; color: #334155; }
        .btn-secondary:hover { background: #cbd5e1; text-decoration: none; }
        .btn-danger { background: var(--danger); color: #fff; }
        .btn-sm { padding: 0.3rem 0.6rem; font-size: 0.8rem; }

        /* Forms */
        .form-group { margin-bottom: 1rem; }
        .form-label { display: block; margin-bottom: 0.35rem; font-weight: 600; font-size: 0.9rem; }
        .form-control {
            width: 100%;
            padding: 0.6rem 0.8rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 0.95rem;
        }
        .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }

        /* Tables */
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }
        .table th, .table td {
            padding: 0.85rem;
            border-bottom: 1px solid var(--border);
            text-align: left;
        }
        .table th { background: #f8fafc; color: #475569; font-weight: 600; }

        /* Grid */
        .grid-3 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.25rem;
        }

        /* Footer */
        .footer {
            border-top: 1px solid var(--border);
            padding: 1.25rem;
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
            background: #fff;
            margin-top: auto;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="{{ route('dashboard') }}" class="navbar-brand">
            <span>🎓</span> KampusLMS
        </a>

        <ul class="navbar-nav">
            @auth
                <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li><a href="{{ route('courses.index') }}">Mata Kuliah</a></li>
                
                @if (in_array(auth()->user()->role, ['mahasiswa', 'student']))
                    <li><a href="{{ route('mahasiswa.grades.index') }}">Nilai Saya</a></li>
                @endif

                <li>
                    <a href="{{ route('notifications.index') }}" style="position: relative;">
                        🔔 Notifikasi
                        @php $unreadCount = auth()->user()->unreadNotifications()->count(); @endphp
                        @if ($unreadCount > 0)
                            <span class="badge badge-danger">{{ $unreadCount }}</span>
                        @endif
                    </a>
                </li>

                <li style="display: flex; align-items: center; gap: 0.5rem; margin-left: 0.5rem;">
                    <span class="badge badge-role">{{ auth()->user()->role }}</span>
                    <span style="font-weight: 600; font-size: 0.9rem;">{{ auth()->user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST" style="display: inline; margin-left: 0.5rem;">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm">Keluar</button>
                    </form>
                </li>
            @else
                <li><a href="{{ route('login') }}" class="btn btn-primary btn-sm">Masuk / Login</a></li>
            @endauth
        </ul>
    </nav>

    <div class="container">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul style="padding-left: 1.2rem; margin: 0;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>

    <footer class="footer">
        &copy; {{ date('Y') }} KampusLMS — Sistem Pembelajaran Daring Terpadu. Siap Produksi.
    </footer>
</body>
</html>
