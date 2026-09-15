@extends('layouts.app')

@section('title', 'Tambah Mata Kuliah')

@section('content')
<h1>Tambah Mata Kuliah</h1>

<form method="POST" action="{{ route('courses.store') }}">
    @csrf

    <div>
        <label>Kode</label>
        <input type="text" name="code" value="{{ old('code') }}">
        @error('code') <p>{{ $message }}</p> @enderror
    </div>

    <div>
        <label>Nama</label>
        <input type="text" name="name" value="{{ old('name') }}">
        @error('name') <p>{{ $message }}</p> @enderror
    </div>

    <div>
        <label>Deskripsi</label>
        <textarea name="description">{{ old('description') }}</textarea>
        @error('description') <p>{{ $message }}</p> @enderror
    </div>

    <div>
        <label>SKS</label>
        <input type="number" name="sks" value="{{ old('sks') }}" min="1" max="6">
        @error('sks') <p>{{ $message }}</p> @enderror
    </div>

    <div>
        <label>Dosen Pengampu</label>
        <select name="lecturer_id">
            <option value="">-- Pilih Dosen --</option>
            @foreach ($lecturers as $lecturer)
                <option value="{{ $lecturer->id }}" {{ old('lecturer_id') == $lecturer->id ? 'selected' : '' }}>
                    {{ $lecturer->name }}
                </option>
            @endforeach
        </select>
        @error('lecturer_id') <p>{{ $message }}</p> @enderror
    </div>

    <div>
        <label>Status</label>
        <select name="status">
            <option value="draft"    {{ old('status') === 'draft'    ? 'selected' : '' }}>Draft</option>
            <option value="active"   {{ old('status') === 'active'   ? 'selected' : '' }}>Aktif</option>
            <option value="archived" {{ old('status') === 'archived' ? 'selected' : '' }}>Diarsipkan</option>
        </select>
        @error('status') <p>{{ $message }}</p> @enderror
    </div>

    <button type="submit">Simpan</button>
    <a href="{{ route('courses.index') }}">Batal</a>
</form>
@endsection
