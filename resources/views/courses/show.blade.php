@extends('layouts.app')

@section('title', 'Detail Mata Kuliah')

@section('content')
    {{-- BUG 4: URL hardcode, bukan route('courses.index') --}}
    <a href="/courses" style="color:#4f46e5;">← Kembali ke Daftar</a>

    {{-- BUG 6: Logika pencarian/filter dilakukan di dalam VIEW, bukan di controller.
                 Ini melanggar prinsip MVC (Model-View-Controller).
                 - View seharusnya HANYA menampilkan data.
                 - Logika bisnis (mencari, menyaring, menghitung) = tugas Controller.
                 - Dampak: sulit di-test, sulit di-maintain, duplikasi kode. --}}
    @php
        // LOGIKA BISNIS YANG SALAH TEMPAT — seharusnya di CourseController@show
        $course = null;
        foreach ($courses as $c) {
            if ($c['id'] == $courseId) {
                $course = $c;
                break;
            }
        }
    @endphp

    @if ($course === null)
        <div style="background:#fee2e2; padding:1rem; border-radius:8px; color:#b91c1c;">
            ❌ Mata kuliah tidak ditemukan.
        </div>
    @else
        <div class="card" style="margin-top:1rem;">
            {{-- BUG 5: {!! !!} = output tanpa HTML escaping → XSS.
                         Buka /courses/3 untuk melihat alert dari XSS payload. --}}
            <h1>{!! $course['nama'] !!}</h1>

            <table style="width:100%; border-collapse:collapse; margin-top:1rem;">
                <tr>
                    <td style="padding:.5rem; color:#555; width:150px;">Kode MK</td>
                    <td style="padding:.5rem; font-weight:bold;">{{ $course['kode'] }}</td>
                </tr>
                <tr style="background:#f9f9f9;">
                    <td style="padding:.5rem; color:#555;">SKS</td>
                    <td style="padding:.5rem; font-weight:bold;">{{ $course['sks'] }}</td>
                </tr>
                <tr>
                    <td style="padding:.5rem; color:#555;">Deskripsi</td>
                    <td style="padding:.5rem;">{{ $course['deskripsi'] }}</td>
                </tr>
            </table>
        </div>
    @endif
@endsection
