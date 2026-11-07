@extends('layouts.app')

@section('title', 'Tambah Materi Baru — ' . $course->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-3">
                <li class="breadcrumb-item"><a href="{{ route('courses.index') }}" class="text-decoration-none">Mata Kuliah</a></li>
                <li class="breadcrumb-item"><a href="{{ route('courses.show', $course) }}" class="text-decoration-none">{{ $course->code }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tambah Materi</li>
            </ol>
        </nav>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h4 class="mb-0 fw-bold text-dark"><i class="bi bi-file-earmark-plus me-2 text-primary"></i> Tambah Materi Pembelajaran</h4>
                <small class="text-muted">Mata Kuliah: <strong>{{ $course->name }} ({{ $course->code }})</strong></small>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('dosen.courses.materials.store', $course) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-3 mb-3">
                        <div class="col-md-9">
                            <label class="form-label fw-semibold">Judul Materi <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" placeholder="Contoh: Modul 01 - Pengantar Laravel 11" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Urutan / Pertemuan</label>
                            <input type="number" name="order" class="form-control @error('order') is-invalid @enderror" value="{{ old('order', 1) }}" min="1">
                            @error('order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Uraian / Ringkasan Materi</label>
                        <textarea name="content" rows="4" class="form-control @error('content') is-invalid @enderror" placeholder="Tuliskan catatan, silabus, atau panduan materi...">{{ old('content') }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Unggah Berkas / Dokumen (Opsional)</label>
                        <input type="file" name="file" class="form-control @error('file') is-invalid @enderror">
                        <div class="form-text small">Dukungan format: PDF, DOC, PPT, ZIP, TXT (Maks. 20 MB)</div>
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Tautan Eksternal / Video URL (Opsional)</label>
                        <input type="url" name="url" class="form-control @error('url') is-invalid @enderror" placeholder="https://youtube.com/... atau https://drive.google.com/..." value="{{ old('url') }}">
                        @error('url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-cloud-arrow-up me-1"></i> Simpan Materi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
