<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = [
        'user_id', 'company_id', 'position',
        'status', 'applied_date', 'job_link', 'notes'
    ];

    protected $casts = [
        'applied_date' => 'date',
    ];

    public static array $statuses = [
        'applied'              => 'Applied',
        'interview_scheduled'  => 'Interview Scheduled',
        'interview_completed'  => 'Interview Completed',
        'offered'              => 'Offered',
        'rejected'             => 'Rejected',
        'accepted'             => 'Accepted',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
