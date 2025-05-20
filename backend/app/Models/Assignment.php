<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Assignment extends Model
{
    protected $table = "assignments";
    protected $appends = ['is_expired'];
    
    use HasFactory;

    protected $fillable = [
        'assigned_by',
        'assigned_to',
        'title',
        'description',
        'due_date',
        'status'
    ];

    public function user() 
    {
        return $this->belongsTo(User::class, 'assigned_by'); // Assigned_by
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'assigned_to')->with('student'); // Assigned_to
    }

    public function getIsExpiredAttribute()
    {
        return Carbon::now()->gt(Carbon::parse($this->due_date));
    }

    public function extensionRequests()
    {
        return $this->hasMany(AssignmentExtensionRequest::class);
    }

}

