@extends('layouts.app')

@section('title', 'Selamat Datang')

@section('content')
<div style="text-align: center; padding: 3rem 1rem;">
    <h1 style="font-size: 2.75rem; font-weight: 800; color: #1e293b; margin-bottom: 1rem; line-height: 1.2;">
        Sistem Pembelajaran Kampus Digital Terintegrasi
    </h1>
    <p style="font-size: 1.15rem; color: #64748b; max-width: 650px; margin: 0 auto 2rem auto;">
        Kelola mata kuliah, materi ajar, pengumpulan tugas daring, dan evaluasi nilai mahasiswa secara aman, terstruktur, dan cepat.
    </p>

    <div style="display: flex; gap: 1rem; justify-content: center; margin-bottom: 3.5rem;">
        @auth
            <a href="{{ route('dashboard') }}" class="btn btn-primary" style="padding: 0.8rem 1.8rem; font-size: 1rem;">
                Buka Dashboard Saya &rarr;
            </a>
        @else
            <a href="{{ route('login') }}" class="btn btn-primary" style="padding: 0.8rem 1.8rem; font-size: 1rem;">
                Masuk ke Akun Anda &rarr;
            </a>
        @endauth
    </div>

    <!-- Demo Credentials Card -->
    <div class="card" style="max-width: 750px; margin: 0 auto; text-align: left; background: #ffffff; border: 1px solid #e2e8f0;">
        <div class="card-header" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem;">
            <h3 class="card-title" style="font-size: 1.1rem; color: #0f172a;">
                🔑 Akun Demo Pengujian Produksi (Siap Pakai)
            </h3>
            <span class="badge badge-success">Password: password</span>
        </div>
        <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 1rem;">
            Gunakan salah satu akun di bawah ini untuk mencoba seluruh fitur aplikasi secara langsung:
        </p>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                <span class="badge badge-role">👨‍🏫 DOSEN</span>
                <p style="font-weight: 700; margin-top: 0.5rem; font-size: 0.95rem;">dosen@kampus.id</p>
                <small style="color: #64748b;">Akses: Buat materi, beri tugas, dan berikan nilai tugas mahasiswa.</small>
            </div>
            <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                <span class="badge badge-role">🎓 MAHASISWA</span>
                <p style="font-weight: 700; margin-top: 0.5rem; font-size: 0.95rem;">mahasiswa@kampus.id</p>
                <small style="color: #64748b;">Akses: Lihat materi, kumpulkan tugas PDF, cek riwayat nilai.</small>
            </div>
            <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                <span class="badge badge-role">🛡️ ADMIN</span>
                <p style="font-weight: 700; margin-top: 0.5rem; font-size: 0.95rem;">admin@kampus.id</p>
                <small style="color: #64748b;">Akses: Manajemen seluruh mata kuliah dan pengguna sistem.</small>
            </div>
        </div>
    </div>
</div>
@endsection
