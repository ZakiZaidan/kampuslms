@extends('layouts.app')

@section('title', 'Daftar Mata Kuliah')

@section('content')
<h1>Daftar Mata Kuliah</h1>

{{-- Form pencarian — nilai dari session, bukan query string --}}
<form method="GET" action="{{ route('courses.index') }}">
    <input type="text" name="q" value="{{ $q }}" placeholder="Cari kode atau nama...">
    <select name="status">
        <option value="">Semua Status</option>
        <option value="draft"    {{ $status === 'draft'    ? 'selected' : '' }}>Draft</option>
        <option value="active"   {{ $status === 'active'   ? 'selected' : '' }}>Aktif</option>
        <option value="archived" {{ $status === 'archived' ? 'selected' : '' }}>Diarsipkan</option>
    </select>
    <button type="submit">Cari</button>
</form>

<a href="{{ route('courses.create') }}">+ Tambah Mata Kuliah</a>

<table>
    <thead>
        <tr>
            <th>Kode</th><th>Nama</th><th>SKS</th><th>Dosen</th><th>Status</th><th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($courses as $course)
        <tr>
            <td>{{ $course->code }}</td>
            <td>{{ $course->name }}</td>
            <td>{{ $course->sks }}</td>
            <td>{{ $course->lecturer?->name ?? '-' }}</td>
            <td>{{ $course->status }}</td>
            <td>
                <a href="{{ route('courses.show', $course) }}">Lihat</a>
                <a href="{{ route('courses.edit', $course) }}">Edit</a>
                <form method="POST" action="{{ route('courses.destroy', $course) }}"
                      onsubmit="return confirm('Yakin hapus mata kuliah ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Hapus</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{ $courses->links() }}
@endsection
