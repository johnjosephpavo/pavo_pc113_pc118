<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Student extends Model
{
    protected $table = "students";
    
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'user_id',
        'qr_code',
        'first_name',
        'last_name',
        'age',
        'gender',
        'address',
        'course',
        'contact_number',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}