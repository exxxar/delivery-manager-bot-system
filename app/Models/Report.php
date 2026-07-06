<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'file_name',
        'file_path',
        'report_type',
        'start_date',
        'end_date',
        'file_size',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'file_size' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDownloadUrlAttribute(): string
    {
        return url('/storage/' . $this->file_path);
    }

    public function deleteFile(): void
    {
        if (Storage::exists($this->file_path)) {
            Storage::delete($this->file_path);
        }
    }

    protected static function booted()
    {
        static::deleting(function (Report $report) {
            $report->deleteFile();
        });
    }
}
