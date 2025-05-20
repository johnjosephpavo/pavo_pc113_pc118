<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentExtensionRequest extends Model
{
    protected $table = "assignment_extension_requests";
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'user_id',
        'reason',
        'requested_due_date',
        'status',
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
