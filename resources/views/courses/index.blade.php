@extends('layouts.app')

@section('title', 'Daftar Mata Kuliah')

@section('content')
<div class="card">
    <div class="card-header">
        <div>
            <h1 class="card-title">Daftar Mata Kuliah</h1>
            <p style="color: #64748b; font-size: 0.9rem;">Seluruh mata kuliah yang tersedia di KampusLMS</p>
        </div>
        @if (in_array(auth()->user()->role ?? '', ['admin', 'dosen', 'lecturer']))
            <a href="{{ route('courses.create') }}" class="btn btn-primary btn-sm">+ Tambah Mata Kuliah</a>
        @endif
    </div>

    <!-- Filter & Search -->
    <form method="GET" action="{{ route('courses.index') }}" style="display: flex; gap: 0.75rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari kode atau nama mata kuliah..." class="form-control" style="max-width: 300px;">
        <select name="status" class="form-control" style="max-width: 180px;">
            <option value="">Semua Status</option>
            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
            <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Diarsipkan</option>
        </select>
        <button type="submit" class="btn btn-secondary">Cari</button>
    </form>

    <div style="overflow-x: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Mata Kuliah</th>
                    <th>SKS</th>
                    <th>Dosen Pengampu</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($courses as $course)
                    <tr>
                        <td><strong>{{ $course->code }}</strong></td>
                        <td>
                            <a href="{{ route('courses.show', $course) }}" style="font-weight: 600;">
                                {{ $course->name }}
                            </a>
                        </td>
                        <td>{{ $course->sks }} SKS</td>
                        <td>{{ $course->lecturer?->name ?? 'Belum Ditentukan' }}</td>
                        <td>
                            <span class="badge {{ $course->status === 'published' ? 'badge-success' : 'badge-warning' }}">
                                {{ $course->status }}
                            </span>
                        </td>
                        <td style="display: flex; gap: 0.35rem; align-items: center;">
                            <a href="{{ route('courses.show', $course) }}" class="btn btn-secondary btn-sm">Lihat</a>

                            @can('update', $course)
                                <a href="{{ route('courses.edit', $course) }}" class="btn btn-secondary btn-sm">Edit</a>
                            @endcan

                            @can('delete', $course)
                                <form method="POST" action="{{ route('courses.destroy', $course) }}" onsubmit="return confirm('Yakin hapus mata kuliah ini?')" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #64748b; padding: 2rem;">
                            Tidak ada mata kuliah yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if (method_exists($courses, 'links'))
        <div style="margin-top: 1.5rem;">
            {{ $courses->links() }}
        </div>
    @endif
</div>
@endsection
