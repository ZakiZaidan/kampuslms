<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // BUG 5: $guarded = [] mematikan SELURUH perlindungan mass assignment.
    // Semua kolom tabel users (termasuk role, email_verified_at, remember_token)
    // bisa diisi lewat User::create([...]) atau $user->fill([...]).
    // Dengan ini, siapa pun bisa mengirim role=admin lewat form pendaftaran.
    //
    // Seharusnya:
    // protected $fillable = ['name', 'email', 'password'];
    // (role diisi eksplisit di controller, BUKAN dari input pengguna)
    protected $guarded = [];   // BUG 5

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // Relasi: user sebagai mahasiswa → terdaftar di banyak mata kuliah
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class)->withTimestamps();
    }

    // Relasi: user sebagai dosen → mengampu banyak mata kuliah
    public function taughtCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'lecturer_id');
    }

    // Relasi: user → banyak submission
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }
}
