@extends('layouts.app')

@section('title', 'Rekap Nilai Saya')

@section('content')
<div style="max-width: 900px; margin: 20px auto; padding: 0 15px;">
    <h2>Daftar Nilai Tugas Saya</h2>

    @if ($submissions->isEmpty())
        <p style="color: #888;">Anda belum memiliki riwayat pengumpulan tugas.</p>
    @else
        <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
            <thead>
                <tr style="background: #f7f7f7; text-align: left;">
                    <th style="padding: 10px; border: 1px solid #ddd;">Mata Kuliah</th>
                    <th style="padding: 10px; border: 1px solid #ddd;">Tugas</th>
                    <th style="padding: 10px; border: 1px solid #ddd;">Dikumpulkan Pada</th>
                    <th style="padding: 10px; border: 1px solid #ddd;">Status</th>
                    <th style="padding: 10px; border: 1px solid #ddd;">Nilai</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($submissions as $submission)
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;">
                            {{ $submission->assignment->course->name }} ({{ $submission->assignment->course->code }})
                        </td>
                        <td style="padding: 10px; border: 1px solid #ddd;">
                            {{ $submission->assignment->title }}
                        </td>
                        <td style="padding: 10px; border: 1px solid #ddd; color: #555;">
                            {{ $submission->submitted_at?->format('d M Y H:i') ?? '-' }}
                        </td>
                        <td style="padding: 10px; border: 1px solid #ddd;">
                            @if ($submission->is_late)
                                <span style="color: red; font-weight: bold;">Terlambat</span>
                            @else
                                <span style="color: green;">Tepat Waktu</span>
                            @endif
                        </td>
                        <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold;">
                            {{ $submission->grade?->score ?? 'Belum Dinilai' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- BUG 5: Jika controller belum menggunakan paginate(), pemanggilan links() akan error jika tidak dicek --}}
        @if (method_exists($submissions, 'links'))
            <div style="margin-top: 20px;">
                {{ $submissions->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
