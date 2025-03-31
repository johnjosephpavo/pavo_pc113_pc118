<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class student extends Model
{
    use HasFactory, HasApiTokens;
    protected $fillable =
    [
        'first_name',
        'last_name',
        'age',
        'gender',
        'address',
        'email',
        'course',
        'contact_number',
    ];
}