@extends('layouts.app')

@section('title', 'Edit Mata Kuliah')

@section('content')
<h1>Edit Mata Kuliah</h1>

{{-- BUG 6: @csrf DIHILANGKAN dari form ini.
     Akibatnya: submit form edit akan menghasilkan error 419 (CSRF token mismatch).
     Seharusnya ada direktif @csrf di dalam form. --}}
<form method="POST" action="{{ route('courses.update', $course) }}">
    @method('PUT')

    <div>
        <label>Kode</label>
        <input type="text" name="code" value="{{ old('code', $course->code) }}">
        @error('code') <p>{{ $message }}</p> @enderror
    </div>

    <div>
        <label>Nama</label>
        <input type="text" name="name" value="{{ old('name', $course->name) }}">
        @error('name') <p>{{ $message }}</p> @enderror
    </div>

    <div>
        <label>Deskripsi</label>
        <textarea name="description">{{ old('description', $course->description) }}</textarea>
        @error('description') <p>{{ $message }}</p> @enderror
    </div>

    <div>
        <label>SKS</label>
        <input type="number" name="sks" value="{{ old('sks', $course->sks) }}" min="1" max="6">
        @error('sks') <p>{{ $message }}</p> @enderror
    </div>

    <div>
        <label>Dosen Pengampu</label>
        <select name="lecturer_id">
            <option value="">-- Pilih Dosen --</option>
            @foreach ($lecturers as $lecturer)
                <option value="{{ $lecturer->id }}"
                    {{ old('lecturer_id', $course->lecturer_id) == $lecturer->id ? 'selected' : '' }}>
                    {{ $lecturer->name }}
                </option>
            @endforeach
        </select>
        @error('lecturer_id') <p>{{ $message }}</p> @enderror
    </div>

    <div>
        <label>Status</label>
        <select name="status">
            <option value="draft"    {{ old('status', $course->status) === 'draft'    ? 'selected' : '' }}>Draft</option>
            <option value="active"   {{ old('status', $course->status) === 'active'   ? 'selected' : '' }}>Aktif</option>
            <option value="archived" {{ old('status', $course->status) === 'archived' ? 'selected' : '' }}>Diarsipkan</option>
        </select>
        @error('status') <p>{{ $message }}</p> @enderror
    </div>

    <button type="submit">Perbarui</button>
    <a href="{{ route('courses.show', $course) }}">Batal</a>
</form>
@endsection
