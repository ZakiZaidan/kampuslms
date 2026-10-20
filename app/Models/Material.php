<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Material extends Model
{
    use HasFactory;

    // BUG 6 (w09): Tidak ada static::booted() dengan Storage::delete() saat deleting.
    // Berkas fisik di storage tidak ikut terhapus ketika record dihapus.
    // Solusi: tambahkan booted() dengan static::deleting(fn($m) => Storage::delete($m->file_path))
    protected $fillable = ['course_id', 'title', 'content', 'file_path', 'original_name', 'file_size', 'url', 'order'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
