@extends('layouts.app')

@section('title', 'Tambah Mata Kuliah')

@section('content')
    <h1>➕ Tambah Mata Kuliah</h1>
    <div class="card">
        <p>Halaman ini adalah form tambah mata kuliah. (Khusus untuk eksperimen route ordering.)</p>
        <p>Jika Anda melihat ini, route <code>/courses/create</code> berhasil ditemukan <strong>sebelum</strong> route <code>/courses/{course}</code>.</p>
    </div>
@endsection
