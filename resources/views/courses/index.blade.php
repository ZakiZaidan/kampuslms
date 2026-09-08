@extends('layouts.app')

@section('title', 'Daftar Mata Kuliah')

@section('content')
    <h1>📚 Daftar Mata Kuliah</h1>
    <div class="alert-info">
        Total: <strong>{{ count($courses) }}</strong> mata kuliah tersedia.
    </div>
    @foreach($courses as $course)
        <div class="card">
            <h2 style="margin:0 0 .5rem;">{{ $course['nama'] }}</h2>
            <p style="margin:0; color:#555;">SKS: {{ $course['sks'] }} &nbsp;|&nbsp; Kode: {{ $course['kode'] }}</p>
            <a href="{{ route('courses.show', $course['id']) }}" class="btn btn-primary" style="margin-top:.75rem;">
                Lihat Detail
            </a>
        </div>
    @endforeach
@endsection
