@extends('layouts.app')

@section('title', 'Tambah Mata Kuliah Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-3">
                <li class="breadcrumb-item"><a href="{{ route('courses.index') }}" class="text-decoration-none">Mata Kuliah</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tambah Baru</li>
            </ol>
        </nav>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h4 class="mb-0 fw-bold text-dark"><i class="bi bi-mortarboard-fill me-2 text-primary"></i> Tambah Mata Kuliah</h4>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('courses.store') }}">
                    @csrf

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Kode Mata Kuliah <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" placeholder="Contoh: IF101" value="{{ old('code') }}" required>
                            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Nama Mata Kuliah <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Contoh: Pemrograman Web Lanjut" value="{{ old('name') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">SKS <span class="text-danger">*</span></label>
                            <input type="number" name="sks" class="form-control @error('sks') is-invalid @enderror" value="{{ old('sks', 3) }}" min="1" max="6" required>
                            @error('sks') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Dosen Pengampu <span class="text-danger">*</span></label>
                            <select name="lecturer_id" class="form-select @error('lecturer_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Dosen --</option>
                                @foreach ($lecturers as $lecturer)
                                    <option value="{{ $lecturer->id }}" {{ old('lecturer_id', auth()->user()->role === 'dosen' ? auth()->id() : '') == $lecturer->id ? 'selected' : '' }}>
                                        {{ $lecturer->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('lecturer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="archived" {{ old('status') === 'archived' ? 'selected' : '' }}>Diarsipkan</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Deskripsi & Silabus</label>
                        <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror" placeholder="Tuliskan gambaran umum materi perkuliahan...">{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <a href="{{ route('courses.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-save me-1"></i> Simpan Mata Kuliah
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
