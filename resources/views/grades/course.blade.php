@extends('layouts.app')

@section('title', 'Rekap Nilai — ' . $course->name)

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('courses.index') }}" class="text-decoration-none">Mata Kuliah</a></li>
                <li class="breadcrumb-item"><a href="{{ route('courses.show', $course) }}" class="text-decoration-none">{{ $course->code }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Rekap Nilai</li>
            </ol>
        </nav>
        <h1 class="h2 fw-bold text-gray-800 mb-1">Rekap Nilai: {{ $course->name }}</h1>
        <p class="text-muted mb-0">
            <span class="badge bg-primary me-2">{{ $course->code }}</span>
            <span class="badge bg-secondary me-2">{{ $course->sks }} SKS</span>
            <i class="bi bi-people me-1"></i> Total <strong>{{ $totalStudents }}</strong> Mahasiswa Terdaftar
            <span class="mx-2">•</span>
            Tahun Ajaran: <strong>{{ $academicYear ?? '2026/2027 Ganjil' }}</strong>
        </p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Mata Kuliah
        </a>
    </div>
</div>

@if ($course->assignments->isEmpty())
    <div class="card shadow-sm border-0 text-center py-5">
        <div class="card-body">
            <i class="bi bi-journal-x fs-1 d-block mb-3 text-muted"></i>
            <h5 class="fw-bold text-dark">Belum Ada Tugas</h5>
            <p class="text-muted mb-3">Mata kuliah ini belum memiliki tugas atau penugasan aktif.</p>
            <a href="{{ route('dosen.courses.assignments.create', $course) }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Buat Tugas Pertama
            </a>
        </div>
    </div>
@else
    @foreach ($course->assignments as $assignment)
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-file-earmark-text text-primary me-2"></i> {{ $assignment->title }}
                    </h5>
                    <small class="text-muted">
                        <i class="bi bi-clock me-1"></i> Tenggat: {{ $assignment->due_at?->format('d M Y, H:i') ?? 'Tidak ditentukan' }}
                    </small>
                </div>
                <a href="{{ route('assignments.show', $assignment) }}" class="btn btn-sm btn-outline-primary">
                    Lihat Penugasan <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Mahasiswa</th>
                                <th>Status Waktu</th>
                                <th>Berkas</th>
                                <th>Nilai</th>
                                <th class="pe-4">Umpan Balik (Feedback)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($assignment->submissions as $submission)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $submission->user->name }}</div>
                                        <small class="text-muted">{{ $submission->user->email }}</small>
                                    </td>
                                    <td>
                                        @if ($submission->is_late)
                                            <span class="badge bg-danger-subtle text-danger">Terlambat</span>
                                        @else
                                            <span class="badge bg-success-subtle text-success">Tepat Waktu</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('submissions.download', $submission) }}" class="btn btn-sm btn-light border py-1 px-2">
                                            <i class="bi bi-download me-1"></i> Unduh
                                        </a>
                                    </td>
                                    <td>
                                        @if ($submission->grade)
                                            <span class="badge bg-primary fs-6">{{ $submission->grade->score }}</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">Belum Dinilai</span>
                                        @endif
                                    </td>
                                    <td class="pe-4">
                                        <span class="small text-secondary">{{ $submission->grade?->feedback ?: '-' }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <small>Belum ada pengumpulan tugas untuk penugasan ini.</small>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
@endif
@endsection
