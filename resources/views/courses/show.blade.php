@extends('layouts.app')

@section('title', 'Detail: ' . $course['nama'])

@section('content')
    <a href="{{ route('courses.index') }}" style="color:#4f46e5;">← Kembali ke Daftar</a>
    <div class="card" style="margin-top:1rem;">
        <h1>{{ $course['nama'] }}</h1>
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
@endsection
