<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Student;
use App\Models\Role;

class User extends Authenticatable
{
    protected $table = "users";
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens ;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'profile_image',
        'role',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(){
        
        return $this->belongsTo(Role::class);
    }

    public function student()
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    public function assignedAssignments()
    {
        return $this->hasMany(Assignment::class, 'assigned_by');
    }

    public function receivedAssignments()
    {
        return $this->hasMany(Assignment::class, 'assigned_to');
    }

    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }


}
