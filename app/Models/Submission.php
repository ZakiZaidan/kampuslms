<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Submission extends Model
{
    use HasFactory;

    // BUG 6 (w09): Tidak ada static::booted() dengan Storage::delete() saat deleting.
    // Akibatnya, jika submission dihapus dari database, berkas fisik di storage
    // tetap ada dan menjadi "yatim" — tidak bisa diakses siapa pun tapi makan disk.
    protected $fillable = ['assignment_id', 'user_id', 'file_path', 'original_name', 'file_size', 'is_late', 'note', 'submitted_at'];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'is_late'      => 'boolean',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grade(): HasOne
    {
        return $this->hasOne(Grade::class);
    }
}
