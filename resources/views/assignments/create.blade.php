@extends('layouts.app')

@section('title', 'Buat Tugas Baru — ' . $course->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-3">
                <li class="breadcrumb-item"><a href="{{ route('courses.index') }}" class="text-decoration-none">Mata Kuliah</a></li>
                <li class="breadcrumb-item"><a href="{{ route('courses.show', $course) }}" class="text-decoration-none">{{ $course->code }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Buat Tugas Baru</li>
            </ol>
        </nav>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h4 class="mb-0 fw-bold text-dark"><i class="bi bi-journal-plus me-2 text-success"></i> Buat Tugas Baru</h4>
                <small class="text-muted">Mata Kuliah: <strong>{{ $course->name }} ({{ $course->code }})</strong></small>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('dosen.courses.assignments.store', $course) }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Judul Tugas <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" placeholder="Contoh: Tugas 1: Desain Basis Data Relasional" value="{{ old('title') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Batas Waktu Pengumpulan (Tenggat)</label>
                        <input type="datetime-local" name="due_at" class="form-control @error('due_at') is-invalid @enderror" value="{{ old('due_at') }}">
                        <div class="form-text small">Kosongkan jika tugas tidak memiliki batas tenggat waktu.</div>
                        @error('due_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Deskripsi & Instruksi Pengerjaan</label>
                        <textarea name="description" rows="5" class="form-control @error('description') is-invalid @enderror" placeholder="Jelaskan instruksi penugasan, format pengumpulan berkas, atau kriteria penilaian...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-success px-4 fw-semibold">
                            <i class="bi bi-send me-1"></i> Publikasikan Tugas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

