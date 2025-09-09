<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'healthcare_professional_id',
        'appointment_start_time',
        'appointment_end_time',
        'status'
    ];

    protected $casts = [
        'appointment_start_time' => 'datetime:Y-m-d H:i',
        'appointment_end_time' => 'datetime:Y-m-d H:i',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function professional()
    {
        return $this->belongsTo(HealthcareProfessional::class, 'healthcare_professional_id');
    }

    public function scopeForProfessionalBetween($query, $professionalId, $start, $end)
    {
        return $query->where('healthcare_professional_id', $professionalId)
            ->where('status', 'booked')
            ->where(function ($q) use ($start, $end) {
                $q->where('appointment_start_time', '<', $end)
                    ->where('appointment_end_time', '>', $start);
            });
    }

    public function getFormattedStartTimeAttribute()
    {
        return $this->appointment_start_time->format('Y-m-d H:i:s');
    }

    public function getFormattedEndTimeAttribute()
    {
        return $this->appointment_end_time->format('Y-m-d H:i:s');
    }

    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('Y-m-d H:i:s');
    }
}
