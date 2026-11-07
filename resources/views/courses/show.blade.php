@extends('layouts.app')

@section('title', $course->name . ' (' . $course->code . ')')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('courses.index') }}" class="text-decoration-none">Mata Kuliah</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $course->code }}</li>
            </ol>
        </nav>
        <div class="d-flex align-items-center gap-2 mb-2">
            <h1 class="h2 fw-bold text-gray-800 mb-0">{{ $course->name }}</h1>
            <span class="badge bg-primary fs-6">{{ $course->code }}</span>
            <span class="badge bg-secondary">{{ $course->sks }} SKS</span>
            <span class="badge {{ $course->status === 'active' ? 'bg-success' : 'bg-warning text-dark' }}">{{ ucfirst($course->status) }}</span>
        </div>
        <p class="text-muted mb-0">
            <i class="bi bi-person-badge me-1"></i> Dosen Pengampu: <strong>{{ $course->lecturer?->name ?? 'Belum Ditentukan' }}</strong>
            <span class="mx-2">•</span>
            <i class="bi bi-people me-1"></i> {{ $course->students->count() }} Mahasiswa Terdaftar
        </p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0 d-flex flex-wrap gap-2 justify-content-md-end">
        @can('update', $course)
            <a href="{{ route('dosen.courses.materials.create', $course) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-file-earmark-plus me-1"></i> + Materi
            </a>
            <a href="{{ route('dosen.courses.assignments.create', $course) }}" class="btn btn-sm btn-outline-success">
                <i class="bi bi-journal-plus me-1"></i> + Tugas
            </a>
            <a href="{{ route('dosen.courses.grades', $course) }}" class="btn btn-sm btn-outline-info">
                <i class="bi bi-award me-1"></i> Rekap Nilai
            </a>
            <a href="{{ route('courses.edit', $course) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-pencil me-1"></i> Edit MK
            </a>
        @endcan
        <a href="{{ route('courses.index') }}" class="btn btn-sm btn-light border">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

@if ($course->description)
    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body">
            <h6 class="fw-bold text-muted mb-2"><i class="bi bi-info-circle me-1"></i> Deskripsi & Silabus</h6>
            <p class="mb-0 text-secondary" style="white-space: pre-line;">{{ $course->description }}</p>
        </div>
    </div>
@endif

<div class="row g-4">
    {{-- Kolom Kiri: Materi Kuliah --}}
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-primary">
                    <i class="bi bi-folder2-open me-2"></i> Materi Pembelajaran
                    <span class="badge bg-primary-subtle text-primary ms-1">{{ $course->materials->count() }}</span>
                </h5>
                @can('update', $course)
                    <a href="{{ route('dosen.courses.materials.create', $course) }}" class="btn btn-xs btn-primary btn-sm">
                        <i class="bi bi-plus-lg"></i> Tambah
                    </a>
                @endcan
            </div>
            <div class="card-body p-3">
                @if ($course->materials->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-journal-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                        <p class="mb-1 fw-semibold">Belum Ada Materi</p>
                        <small>Dosen pengampu belum mengunggah modul atau materi untuk kelas ini.</small>
                    </div>
                @else
                    <div class="list-group list-group-flush gap-2">
                        @foreach ($course->materials as $mat)
                            <div class="list-group-item border rounded-3 p-3 bg-white hover-shadow transition">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="badge bg-secondary-subtle text-secondary me-1">P#{{ $mat->order ?? 1 }}</span>
                                        <h6 class="fw-bold mb-1 d-inline text-dark">{{ $mat->title }}</h6>
                                    </div>
                                    @can('update', $course)
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border-0 py-0 px-2" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                <li>
                                                    <a class="dropdown-item text-secondary" href="{{ route('dosen.materials.edit', $mat) }}">
                                                        <i class="bi bi-pencil me-2"></i> Edit Materi
                                                    </a>
                                                </li>
                                                <li>
                                                    <form action="{{ route('dosen.materials.destroy', $mat) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus materi ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="bi bi-trash me-2"></i> Hapus Materi
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    @endcan
                                </div>

                                @if ($mat->content)
                                    <p class="text-secondary small mb-2" style="white-space: pre-line;">{{ Str::limit($mat->content, 180) }}</p>
                                @endif

                                <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                                    @if ($mat->file_path)
                                        <a href="{{ route('materials.download', $mat) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-download me-1"></i> Unduh Lampiran
                                            @if ($mat->file_size)
                                                <span class="small opacity-75">({{ number_format($mat->file_size / 1024, 0) }} KB)</span>
                                            @endif
                                        </a>
                                    @endif

                                    @if ($mat->url)
                                        <a href="{{ $mat->url }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-box-arrow-up-right me-1"></i> Tautan Materi
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Kolom Kanan: Tugas Kuliah (Assignments) --}}
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-success">
                    <i class="bi bi-journal-text me-2"></i> Tugas & Penugasan
                    <span class="badge bg-success-subtle text-success ms-1">{{ $course->assignments->count() }}</span>
                </h5>
                @can('update', $course)
                    <a href="{{ route('dosen.courses.assignments.create', $course) }}" class="btn btn-xs btn-success btn-sm">
                        <i class="bi bi-plus-lg"></i> Buat Tugas
                    </a>
                @endcan
            </div>
            <div class="card-body p-3">
                @if ($course->assignments->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-clipboard-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                        <p class="mb-1 fw-semibold">Belum Ada Tugas</p>
                        <small>Tidak ada tugas aktif atau pekerjaan rumah untuk mata kuliah ini.</small>
                    </div>
                @else
                    <div class="list-group list-group-flush gap-2">
                        @foreach ($course->assignments as $assign)
                            @php
                                $isDue = $assign->due_at && now()->isAfter($assign->due_at);
                                $mySub = $assign->submissions->where('user_id', auth()->id())->first();
                            @endphp
                            <div class="list-group-item border rounded-3 p-3 bg-white hover-shadow transition">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="fw-bold mb-1">
                                            <a href="{{ route('assignments.show', $assign) }}" class="text-decoration-none text-dark hover-primary">
                                                {{ $assign->title }}
                                            </a>
                                        </h6>
                                        <div class="d-flex align-items-center gap-2 small">
                                            <span class="text-muted">
                                                <i class="bi bi-clock me-1"></i> Tenggat:
                                                <strong>{{ $assign->due_at ? $assign->due_at->format('d M Y, H:i') : 'Tidak ditentukan' }}</strong>
                                            </span>
                                            @if ($assign->due_at)
                                                @if ($isDue)
                                                    <span class="badge bg-danger-subtle text-danger">Tenggat Berakhir</span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-dark">{{ $assign->due_at->diffForHumans() }}</span>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                    @can('update', $course)
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border-0 py-0 px-2" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                <li>
                                                    <form action="{{ route('dosen.assignments.destroy', $assign) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus tugas ini? Data pengumpulan mahasiswa juga akan terhapus.')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="bi bi-trash me-2"></i> Hapus Tugas
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    @endcan
                                </div>

                                @if ($assign->description)
                                    <p class="text-secondary small mb-2" style="white-space: pre-line;">{{ Str::limit($assign->description, 150) }}</p>
                                @endif

                                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                    @if (auth()->user()->role === 'mahasiswa' || auth()->user()->role === 'student')
                                        @if ($mySub)
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i> Sudah Dikumpulkan
                                                @if ($mySub->is_late)
                                                    <span class="text-warning-emphasis">(Terlambat)</span>
                                                @endif
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">
                                                <i class="bi bi-hourglass me-1"></i> Belum Dikumpulkan
                                            </span>
                                        @endif
                                    @else
                                        <span class="small text-muted">
                                            <i class="bi bi-file-earmark-check me-1"></i> {{ $assign->submissions_count }} Pengumpulan
                                        </span>
                                    @endif

                                    <a href="{{ route('assignments.show', $assign) }}" class="btn btn-sm btn-primary">
                                        Lihat Rincian <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
