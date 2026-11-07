@extends('layouts.app')

@section('title', $assignment->title . ' — ' . $course->name)

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('courses.index') }}" class="text-decoration-none">Mata Kuliah</a></li>
                <li class="breadcrumb-item"><a href="{{ route('courses.show', $course) }}" class="text-decoration-none">{{ $course->code }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tugas</li>
            </ol>
        </nav>
        <h1 class="h2 fw-bold text-gray-800 mb-1">{{ $assignment->title }}</h1>
        <p class="text-muted mb-0">
            <span class="badge bg-primary me-2">{{ $course->code }}</span> {{ $course->name }}
            <span class="mx-2">•</span>
            <i class="bi bi-person me-1"></i> Dosen: <strong>{{ $course->lecturer?->name ?? '-' }}</strong>
        </p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Mata Kuliah
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Kolom Rincian Tugas --}}
    <div class="col-lg-{{ in_array(auth()->user()->role, ['mahasiswa', 'student']) ? '7' : '12' }}">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-card-text me-2"></i> Instruksi & Petunjuk Tugas</h5>
                @php
                    $isOverdue = $assignment->due_at && now()->isAfter($assignment->due_at);
                @endphp
                @if ($assignment->due_at)
                    <span class="badge {{ $isOverdue ? 'bg-danger' : 'bg-warning text-dark' }} px-3 py-2">
                        <i class="bi bi-clock me-1"></i> Tenggat: {{ $assignment->due_at->format('d M Y, H:i') }}
                    </span>
                @else
                    <span class="badge bg-secondary px-3 py-2">Tidak ada batas tenggat</span>
                @endif
            </div>
            <div class="card-body">
                <div class="p-3 bg-light rounded-3 text-secondary mb-3" style="white-space: pre-line; line-height: 1.7;">
                    {{ $assignment->description ?: 'Tidak ada deskripsi atau instruksi khusus untuk tugas ini.' }}
                </div>
                <div class="d-flex align-items-center text-muted small">
                    <i class="bi bi-info-circle me-1"></i> Diterbitkan pada: {{ $assignment->created_at->format('d M Y H:i') }}
                </div>
            </div>
        </div>

        {{-- JIKA MAHASISWA: TAMPILKAN STATUS PENGUMPULAN & FORM --}}
        @if (in_array(auth()->user()->role, ['mahasiswa', 'student']))
            @if ($mySubmission)
                <div class="card shadow-sm border-0 mb-4 border-success">
                    <div class="card-header bg-success-subtle text-success-emphasis py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-check2-circle me-2"></i> Tugas Anda Telah Dikumpulkan</h5>
                        @if ($mySubmission->is_late)
                            <span class="badge bg-danger">Terlambat</span>
                        @else
                            <span class="badge bg-success">Tepat Waktu</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <small class="text-muted d-block">Waktu Pengumpulan</small>
                                <strong>{{ $mySubmission->submitted_at ? $mySubmission->submitted_at->format('d M Y, H:i:s') : '-' }}</strong>
                            </div>
                            <div class="col-sm-6">
                                <small class="text-muted d-block">Berkas Lampiran</small>
                                <a href="{{ route('submissions.download', $mySubmission) }}" class="btn btn-sm btn-outline-primary mt-1">
                                    <i class="bi bi-download me-1"></i> {{ $mySubmission->original_name ?? 'Unduh Berkas' }}
                                    @if ($mySubmission->file_size)
                                        <span class="small opacity-75">({{ number_format($mySubmission->file_size / 1024, 0) }} KB)</span>
                                    @endif
                                </a>
                            </div>
                        </div>

                        @if ($mySubmission->note)
                            <div class="mb-3">
                                <small class="text-muted d-block">Catatan Anda:</small>
                                <p class="mb-0 text-secondary bg-light p-2 rounded small">{{ $mySubmission->note }}</p>
                            </div>
                        @endif

                        {{-- Bagian Nilai --}}
                        <div class="p-3 rounded-3 mt-3 {{ $mySubmission->grade ? 'bg-info-subtle border border-info' : 'bg-light border' }}">
                            @if ($mySubmission->grade)
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="fw-bold text-info-emphasis"><i class="bi bi-award me-1"></i> Nilai Tugas:</span>
                                    <span class="display-6 fw-bold text-primary">{{ $mySubmission->grade->score }} <span class="fs-6 text-muted">/ 100</span></span>
                                </div>
                                @if ($mySubmission->grade->feedback)
                                    <div class="mt-2 pt-2 border-top border-info-subtle">
                                        <small class="text-muted d-block fw-semibold">Umpan Balik Dosen:</small>
                                        <p class="mb-0 text-dark small" style="white-space: pre-line;">{{ $mySubmission->grade->feedback }}</p>
                                    </div>
                                @endif
                                <div class="mt-2 text-end">
                                    <small class="text-muted">Dinilai pada: {{ $mySubmission->grade->created_at->format('d M Y H:i') }}</small>
                                </div>
                            @else
                                <div class="d-flex align-items-center text-muted">
                                    <i class="bi bi-hourglass-split fs-4 me-2 text-warning"></i>
                                    <div>
                                        <strong>Menunggu Penilaian</strong>
                                        <div class="small">Dosen belum memberikan nilai untuk tugas yang Anda kumpulkan.</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>

    {{-- Kolom Kanan: Form Pengumpulan untuk Mahasiswa --}}
    @if (in_array(auth()->user()->role, ['mahasiswa', 'student']))
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-primary">
                        <i class="bi bi-upload me-2"></i> {{ $mySubmission ? 'Kumpul Ulang / Revisi' : 'Pengumpulan Tugas' }}
                    </h5>
                </div>
                <div class="card-body p-4">
                    @if ($isOverdue)
                        <div class="alert alert-warning py-2 px-3 small d-flex align-items-center mb-3">
                            <i class="bi bi-exclamation-triangle-fill fs-5 me-2 flex-shrink-0"></i>
                            <div>Tenggat tugas telah lewat. Pengumpulan Anda akan ditandai sebagai <strong>Terlambat</strong>.</div>
                        </div>
                    @endif

                    <form action="{{ route('mahasiswa.submissions.store', $assignment) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Pilih Berkas Tugas <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" required>
                            <div class="form-text small">
                                Format diizinkan: PDF, DOC, DOCX, ZIP, RAR, TXT (Maks. 5 MB)
                            </div>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Catatan Tambahan (Opsional)</label>
                            <textarea name="note" rows="3" class="form-control @error('note') is-invalid @enderror" placeholder="Tuliskan catatan atau keterangan untuk dosen...">{{ old('note') }}</textarea>
                            @error('note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                            <i class="bi bi-cloud-arrow-up me-1"></i> {{ $mySubmission ? 'Unggah Berkas Revisi' : 'Kirimkan Tugas' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- JIKA DOSEN ATAU ADMIN: TAMPILKAN TABEL DAFTAR PENGUMPULAN & FORM PENILAIAN --}}
@if (in_array(auth()->user()->role, ['dosen', 'lecturer', 'admin']))
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-people me-2"></i> Daftar Pengumpulan Mahasiswa</h5>
                <small class="text-muted">Total {{ $assignment->submissions->count() }} mahasiswa telah mengumpulkan tugas ini.</small>
            </div>
            <a href="{{ route('dosen.courses.grades', $course) }}" class="btn btn-sm btn-outline-info">
                <i class="bi bi-award me-1"></i> Lihat Rekap Nilai Lengkap
            </a>
        </div>
        <div class="card-body p-0">
            @if ($assignment->submissions->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                    <p class="mb-1 fw-semibold">Belum Ada Pengumpulan</p>
                    <small>Belum ada mahasiswa yang mengumpulkan berkas untuk tugas ini.</small>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Mahasiswa</th>
                                <th>Waktu Pengumpulan</th>
                                <th>Status</th>
                                <th>Berkas</th>
                                <th>Catatan</th>
                                <th>Nilai</th>
                                <th class="pe-4 text-end">Aksi Penilaian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($assignment->submissions as $sub)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $sub->user->name }}</div>
                                        <small class="text-muted">{{ $sub->user->email }}</small>
                                    </td>
                                    <td>
                                        <span class="small">{{ $sub->submitted_at ? $sub->submitted_at->format('d M Y, H:i') : '-' }}</span>
                                    </td>
                                    <td>
                                        @if ($sub->is_late)
                                            <span class="badge bg-danger-subtle text-danger">Terlambat</span>
                                        @else
                                            <span class="badge bg-success-subtle text-success">Tepat Waktu</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('submissions.download', $sub) }}" class="btn btn-sm btn-outline-primary py-1 px-2">
                                            <i class="bi bi-download me-1"></i> Unduh
                                            @if ($sub->file_size)
                                                <small>({{ number_format($sub->file_size / 1024, 0) }}KB)</small>
                                            @endif
                                        </a>
                                    </td>
                                    <td>
                                        <small class="text-secondary">{{ Str::limit($sub->note ?: '-', 40) }}</small>
                                    </td>
                                    <td>
                                        @if ($sub->grade)
                                            <span class="badge bg-primary fs-6">{{ $sub->grade->score }}</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">Belum Dinilai</span>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-end">
                                        <button class="btn btn-sm btn-outline-success" type="button" data-bs-toggle="collapse" data-bs-target="#gradeForm-{{ $sub->id }}">
                                            <i class="bi bi-pencil-square me-1"></i> {{ $sub->grade ? 'Edit Nilai' : 'Beri Nilai' }}
                                        </button>
                                    </td>
                                </tr>
                                <tr class="collapse bg-light" id="gradeForm-{{ $sub->id }}">
                                    <td colspan="7" class="p-4">
                                        <div class="card border-0 shadow-sm p-3">
                                            <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-award me-1"></i> Penilaian Tugas: {{ $sub->user->name }}</h6>
                                            <form action="{{ route('dosen.submissions.grade', $sub) }}" method="POST">
                                                @csrf
                                                <div class="row g-3">
                                                    <div class="col-md-3">
                                                        <label class="form-label fw-semibold">Nilai Angka (0 - 100) <span class="text-danger">*</span></label>
                                                        <input type="number" name="score" class="form-control" min="0" max="100" step="1" required value="{{ old('score', $sub->grade?->score) }}">
                                                    </div>
                                                    <div class="col-md-7">
                                                        <label class="form-label fw-semibold">Umpan Balik / Catatan Evaluasi</label>
                                                        <input type="text" name="feedback" class="form-control" placeholder="Tuliskan apresiasi atau saran perbaikan..." value="{{ old('feedback', $sub->grade?->feedback) }}">
                                                    </div>
                                                    <div class="col-md-2 d-flex align-items-end">
                                                        <button type="submit" class="btn btn-success w-100 py-2 fw-semibold">
                                                            <i class="bi bi-check-lg me-1"></i> Simpan
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endif
@endsection
