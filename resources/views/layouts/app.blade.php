<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'KampusLMS')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: sans-serif; margin: 0; padding: 0; background: #f5f5f5; }
        nav { background: #1a1a2e; color: white; padding: 1rem 2rem; display: flex; gap: 1rem; }
        nav a { color: white; text-decoration: none; font-weight: bold; }
        nav a:hover { text-decoration: underline; }
        main { padding: 2rem; max-width: 900px; margin: 0 auto; }
        h1 { color: #1a1a2e; }
        .card { background: white; border-radius: 8px; padding: 1.5rem; margin-bottom: 1rem;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .btn { display: inline-block; padding: .5rem 1.2rem; border-radius: 6px;
               text-decoration: none; font-weight: bold; cursor: pointer; }
        .btn-primary { background: #4f46e5; color: white; }
        .btn-primary:hover { background: #3730a3; }
        .alert-info { background: #e0f2fe; border-left: 4px solid #0284c7;
                      padding: .75rem 1rem; border-radius: 4px; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <nav>
        <a href="{{ url('/') }}">🏠 Home</a>
        <a href="{{ route('courses.index') }}">📚 Mata Kuliah</a>
        <a href="{{ route('courses.create') }}">➕ Tambah MK</a>
    </nav>
    <main>
        @yield('content')
    </main>
</body>
</html>
