@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 800; color: #0f172a;">
                Selamat Datang, {{ auth()->user()->name }}! 👋
            </h1>
            <p style="color: #64748b; font-size: 0.95rem;">
                Anda masuk sebagai <span class="badge badge-role">{{ auth()->user()->role }}</span> di KampusLMS.
            </p>
        </div>
        <div>
            @if (in_array(auth()->user()->role, ['dosen', 'lecturer', 'admin']))
                <a href="{{ route('courses.create') }}" class="btn btn-primary">
                    + Tambah Mata Kuliah
                </a>
            @endif
        </div>
    </div>
</div>

<!-- Dashboard Stats -->
@php
    $user = auth()->user();
    $role = strtolower($user->role);
@endphp

@if (in_array($role, ['dosen', 'lecturer']))
    <!-- TAMPILAN DOSEN -->
    @php
        $taughtCourses = $user->taughtCourses()->withCount(['students', 'assignments'])->get();
        $totalTugas = $taughtCourses->sum('assignments_count');
        $totalMhs = $taughtCourses->sum('students_count');
    @endphp

    <div class="grid-3" style="margin-bottom: 2rem;">
        <div class="card" style="margin-bottom: 0;">
            <p style="color: #64748b; font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">Mata Kuliah Diampu</p>
            <h2 style="font-size: 2rem; font-weight: 800; color: #2563eb; margin-top: 0.25rem;">{{ $taughtCourses->count() }}</h2>
        </div>
        <div class="card" style="margin-bottom: 0;">
            <p style="color: #64748b; font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">Total Tugas Aktif</p>
            <h2 style="font-size: 2rem; font-weight: 800; color: #059669; margin-top: 0.25rem;">{{ $totalTugas }}</h2>
        </div>
        <div class="card" style="margin-bottom: 0;">
            <p style="color: #64748b; font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">Total Mahasiswa Terdaftar</p>
            <h2 style="font-size: 2rem; font-weight: 800; color: #d97706; margin-top: 0.25rem;">{{ $totalMhs }}</h2>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Mata Kuliah yang Anda Ampu</h2>
        </div>

        @if ($taughtCourses->isEmpty())
            <p style="color: #64748b;">Anda belum mengampu mata kuliah. Silakan buat mata kuliah baru.</p>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Mata Kuliah</th>
                        <th>SKS</th>
                        <th>Mahasiswa</th>
                        <th>Tugas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($taughtCourses as $course)
                        <tr>
                            <td><strong>{{ $course->code }}</strong></td>
                            <td>
                                <a href="{{ route('courses.show', $course) }}" style="font-weight: 600;">
                                    {{ $course->name }}
                                </a>
                            </td>
                            <td>{{ $course->sks }} SKS</td>
                            <td>{{ $course->students_count }} orang</td>
                            <td>{{ $course->assignments_count }} tugas</td>
                            <td style="display: flex; gap: 0.5rem;">
                                <a href="{{ route('courses.show', $course) }}" class="btn btn-secondary btn-sm">Buka Kelas</a>
                                <a href="{{ route('courses.grades', $course) }}" class="btn btn-primary btn-sm">📊 Rekap Nilai</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

@elseif (in_array($role, ['mahasiswa', 'student']))
    <!-- TAMPILAN MAHASISWA -->
    @php
        $enrolledCourses = $user->courses()->with(['lecturer', 'assignments'])->get();
        $mySubmissions = $user->submissions()->with('grade')->get();
        $totalSelesai = $mySubmissions->count();
        $totalTugas = $enrolledCourses->sum(fn($c) => $c->assignments->count());
        $rataNilai = $mySubmissions->whereNotNull('grade')->avg('grade.score');
    @endphp

    <div class="grid-3" style="margin-bottom: 2rem;">
        <div class="card" style="margin-bottom: 0;">
            <p style="color: #64748b; font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">Mata Kuliah Terdaftar</p>
            <h2 style="font-size: 2rem; font-weight: 800; color: #2563eb; margin-top: 0.25rem;">{{ $enrolledCourses->count() }}</h2>
        </div>
        <div class="card" style="margin-bottom: 0;">
            <p style="color: #64748b; font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">Tugas Selesai Dikumpulkan</p>
            <h2 style="font-size: 2rem; font-weight: 800; color: #059669; margin-top: 0.25rem;">{{ $totalSelesai }} / {{ $totalTugas }}</h2>
        </div>
        <div class="card" style="margin-bottom: 0;">
            <p style="color: #64748b; font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">Rata-rata Nilai Anda</p>
            <h2 style="font-size: 2rem; font-weight: 800; color: #d97706; margin-top: 0.25rem;">
                {{ $rataNilai ? number_format($rataNilai, 1) : '-' }}
            </h2>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Mata Kuliah Semester Ini</h2>
            <a href="{{ route('mahasiswa.grades.index') }}" class="btn btn-secondary btn-sm">Lihat Semua Nilai &rarr;</a>
        </div>

        @if ($enrolledCourses->isEmpty())
            <p style="color: #64748b;">Anda belum terdaftar pada mata kuliah apa pun.</p>
        @else
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
                @foreach ($enrolledCourses as $course)
                    <div style="border: 1px solid var(--border); border-radius: 8px; padding: 1.25rem; background: #fff;">
                        <span class="badge badge-role">{{ $course->code }} • {{ $course->sks }} SKS</span>
                        <h3 style="font-size: 1.15rem; margin: 0.5rem 0 0.25rem 0;">
                            <a href="{{ route('courses.show', $course) }}">{{ $course->name }}</a>
                        </h3>
                        <p style="color: #64748b; font-size: 0.85rem; margin-bottom: 1rem;">
                            Dosen: <strong>{{ $course->lecturer?->name ?? 'Belum Ditentukan' }}</strong>
                        </p>
                        <a href="{{ route('courses.show', $course) }}" class="btn btn-primary btn-sm" style="width: 100%;">
                            Buka Materi & Tugas &rarr;
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

@else
    <!-- TAMPILAN ADMIN -->
    @php
        $allCourses = \App\Models\Course::with('lecturer')->withCount(['students', 'assignments'])->get();
    @endphp

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Semua Mata Kuliah (Akses Admin)</h2>
            <a href="{{ route('courses.create') }}" class="btn btn-primary btn-sm">+ Tambah Baru</a>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Mata Kuliah</th>
                    <th>Dosen Pengampu</th>
                    <th>SKS</th>
                    <th>Mahasiswa</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($allCourses as $course)
                    <tr>
                        <td><strong>{{ $course->code }}</strong></td>
                        <td><a href="{{ route('courses.show', $course) }}">{{ $course->name }}</a></td>
                        <td>{{ $course->lecturer?->name ?? '-' }}</td>
                        <td>{{ $course->sks }} SKS</td>
                        <td>{{ $course->students_count }} orang</td>
                        <td>
                            <a href="{{ route('courses.show', $course) }}" class="btn btn-secondary btn-sm">Detail</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
