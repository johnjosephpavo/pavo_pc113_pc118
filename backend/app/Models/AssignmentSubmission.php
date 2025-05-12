<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssignmentSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'user_id',
        'answer',
        'submitted_at',
    ];

    // Relationship to the student who submitted
    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relationship to the original assignment
    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }
}
