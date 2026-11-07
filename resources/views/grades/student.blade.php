@extends('layouts.app')

@section('title', 'Rekap Nilai Tugas Saya')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h1 class="h2 fw-bold text-gray-800 mb-1"><i class="bi bi-award me-2 text-warning"></i> Rekap Nilai Tugas Saya</h1>
        <p class="text-muted mb-0">Daftar seluruh riwayat tugas yang telah Anda kumpulkan dan hasil evaluasi dosen.</p>
    </div>
</div>

@if ($submissions->isEmpty())
    <div class="card shadow-sm border-0 text-center py-5">
        <div class="card-body">
            <i class="bi bi-journal-x fs-1 d-block mb-3 text-muted"></i>
            <h5 class="fw-bold text-dark">Belum Ada Riwayat Tugas</h5>
            <p class="text-muted mb-3">Anda belum mengumpulkan tugas untuk mata kuliah yang Anda ikuti.</p>
            <a href="{{ route('courses.index') }}" class="btn btn-primary">
                <i class="bi bi-search me-1"></i> Jelajahi Mata Kuliah
            </a>
        </div>
    </div>
@else
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Mata Kuliah</th>
                            <th>Judul Tugas</th>
                            <th>Waktu Pengumpulan</th>
                            <th>Status Waktu</th>
                            <th>Nilai</th>
                            <th>Umpan Balik Dosen</th>
                            <th class="pe-4 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($submissions as $submission)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ $submission->assignment->course->name }}</div>
                                    <span class="badge bg-secondary-subtle text-secondary">{{ $submission->assignment->course->code }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $submission->assignment->title }}</div>
                                </td>
                                <td>
                                    <span class="small text-muted">{{ $submission->submitted_at?->format('d M Y, H:i') ?? '-' }}</span>
                                </td>
                                <td>
                                    @if ($submission->is_late)
                                        <span class="badge bg-danger-subtle text-danger">Terlambat</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success">Tepat Waktu</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($submission->grade)
                                        <span class="badge bg-primary fs-6">{{ $submission->grade->score }} <span class="small opacity-75">/ 100</span></span>
                                    @else
                                        <span class="badge bg-warning-subtle text-dark">Belum Dinilai</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-secondary">{{ Str::limit($submission->grade?->feedback ?: '-', 45) }}</small>
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="{{ route('assignments.show', $submission->assignment) }}" class="btn btn-sm btn-outline-primary">
                                        Rincian <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if (method_exists($submissions, 'hasPages') && $submissions->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $submissions->links() }}
            </div>
        @endif
    </div>
@endif
@endsection
