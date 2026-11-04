<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KampusLMS — @yield('title', 'Dashboard')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <nav style="display: flex; gap: 15px; align-items: center; padding: 10px; background: #f4f4f4;">
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <a href="{{ route('courses.index') }}">Mata Kuliah</a>

        @auth
            <a href="{{ route('notifications.index') }}" style="position: relative; text-decoration: none;">
                🔔 Notifikasi
                @php $unreadCount = auth()->user()->unreadNotifications()->count(); @endphp
                @if ($unreadCount > 0)
                    <span style="background: #e3342f; color: white; border-radius: 9999px; padding: 2px 7px; font-size: 11px; font-weight: bold; margin-left: 4px;">
                        {{ $unreadCount }}
                    </span>
                @endif
            </a>
        @endauth
    </nav>

    <main>
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @yield('content')
    </main>
</body>
</html>
