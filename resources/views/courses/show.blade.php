@extends('layouts.app')

@section('title', $course->name)

@section('content')
<h1>{{ $course->name }}</h1>
<p><strong>Kode:</strong> {{ $course->code }}</p>
<p><strong>SKS:</strong> {{ $course->sks }}</p>
<p><strong>Status:</strong> {{ $course->status }}</p>
<p><strong>Dosen:</strong> {{ $course->lecturer?->name ?? '-' }}</p>
<p><strong>Deskripsi:</strong> {{ $course->description ?? '-' }}</p>

<a href="{{ route('courses.edit', $course) }}">Edit</a>
<a href="{{ route('courses.index') }}">Kembali</a>
@endsection
