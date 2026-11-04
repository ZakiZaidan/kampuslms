@extends('layouts.app')

@section('title', 'Rekap Nilai — ' . $course->name)

@section('content')
<div style="max-width: 1000px; margin: 20px auto; padding: 0 15px;">
    <div style="margin-bottom: 20px;">
        <h2>Rekap Nilai: {{ $course->name }} ({{ $course->code }})</h2>
        <p style="color: #666; margin: 4px 0;">
            Aplikasi: <strong>{{ $appName ?? '[KOSONG - BUG 6 env() null]' }}</strong> |
            Tahun Ajaran: <strong>{{ $academicYear ?? '-' }}</strong>
        </p>
        <p style="color: #444; margin: 4px 0;">
            Total Mahasiswa Terdaftar: <strong>{{ $totalStudents }}</strong> orang
            <small style="color: #888;">(Dihitung via count($course->students) — BUG 2)</small>
        </p>
    </div>

    @if ($course->assignments->isEmpty())
        <p style="color: #888;">Belum ada tugas pada mata kuliah ini.</p>
    @else
        {{-- BUG 1: Loop bersarang yang memicu N+1 query jika relasi tidak di-eager load --}}
        @foreach ($course->assignments as $assignment)
            <div style="border: 1px solid #ccc; border-radius: 6px; padding: 15px; margin-bottom: 20px; background: #fff;">
                <h3 style="margin-top: 0;">{{ $assignment->title }}</h3>
                <small style="color: #888;">Tenggat: {{ $assignment->due_at?->format('d M Y H:i') ?? 'Tidak ada' }}</small>

                <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                    <thead>
                        <tr style="background: #f7f7f7; text-align: left;">
                            <th style="padding: 8px; border: 1px solid #ddd;">Mahasiswa</th>
                            <th style="padding: 8px; border: 1px solid #ddd;">Status Waktu</th>
                            <th style="padding: 8px; border: 1px solid #ddd;">Nilai</th>
                            <th style="padding: 8px; border: 1px solid #ddd;">Feedback</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($assignment->submissions as $submission)
                            <tr>
                                {{-- BUG 1: Mengakses $submission->user memicu N query tambahan --}}
                                <td style="padding: 8px; border: 1px solid #ddd;">
                                    {{ $submission->user->name }}
                                </td>
                                <td style="padding: 8px; border: 1px solid #ddd;">
                                    @if ($submission->is_late)
                                        <span style="color: red;">Terlambat</span>
                                    @else
                                        <span style="color: green;">Tepat Waktu</span>
                                    @endif
                                </td>
                                {{-- BUG 1: Mengakses $submission->grade memicu N query tambahan --}}
                                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold;">
                                    {{ $submission->grade?->score ?? 'Belum Dinilai' }}
                                </td>
                                <td style="padding: 8px; border: 1px solid #ddd; color: #555;">
                                    {{ $submission->grade?->feedback ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="padding: 8px; border: 1px solid #ddd; text-align: center; color: #888;">
                                    Belum ada pengumpulan tugas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif
</div>
@endsection
