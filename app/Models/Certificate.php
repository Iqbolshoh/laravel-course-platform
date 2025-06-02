<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Certificate extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'certificate_number',
        'issued_at',
    ];

    protected $casts = [
        'issued_at' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public static function generateCertificateNumber(): string
    {
        do {
            $certificateNumber = 'CERT-' . Str::upper(Str::random(8));
        } while (self::where('certificate_number', $certificateNumber)->exists());

        return $certificateNumber;
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($certificate) {
            if (empty($certificate->certificate_number)) {
                $certificate->certificate_number = self::generateCertificateNumber();
            }
        });
    }
}