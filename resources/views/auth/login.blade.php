@extends('layouts.app')

@section('title', 'Masuk ke Akun')

@section('content')
<div style="max-width: 440px; margin: 3rem auto;">
    <div class="card">
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.5rem; font-weight: 800; color: #0f172a;">Masuk ke KampusLMS</h2>
            <p style="color: #64748b; font-size: 0.9rem; margin-top: 0.25rem;">Gunakan email dan kata sandi akun Anda</p>
        </div>

        <!-- 1-Click Quick Fill Demo Buttons -->
        <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 0.85rem; margin-bottom: 1.5rem;">
            <p style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 0.5rem; text-align: center;">
                ⚡ Klik Cepat untuk Isi Akun Demo:
            </p>
            <div style="display: flex; gap: 0.5rem; justify-content: center;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="fillLogin('dosen@kampus.id', 'password')">
                    👨‍🏫 Dosen
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="fillLogin('mahasiswa@kampus.id', 'password')">
                    🎓 Mahasiswa
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="fillLogin('admin@kampus.id', 'password')">
                    🛡️ Admin
                </button>
            </div>
        </div>

        <form action="{{ route('login') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">Alamat Email</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required autofocus placeholder="nama@kampus.id">
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Kata Sandi</label>
                <input type="password" name="password" id="password" class="form-control" required placeholder="••••••••">
            </div>

            <div class="form-group" style="display: flex; align-items: center; justify-content: space-between;">
                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: #64748b; cursor: pointer;">
                    <input type="checkbox" name="remember"> Ingat Saya
                </label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 1rem; margin-top: 0.5rem;">
                Masuk Sekarang
            </button>
        </form>
    </div>
</div>

<script>
    function fillLogin(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
    }
</script>
@endsection
