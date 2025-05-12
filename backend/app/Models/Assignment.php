<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Assignment extends Model
{
    protected $table = "assignments";
    
    use HasFactory;

    protected $fillable = [
        'assigned_by',
        'assigned_to',
        'title',
        'description',
        'due_date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'assigned_to')->with('student');
    }

}

