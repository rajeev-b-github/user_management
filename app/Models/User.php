<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;


class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'user_type',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    protected $table = 'users';
    protected $guarded = ['id'];

    public function address()
    {
        return $this->hasOne(Address::class);
    }
    public function subject()
    {
        return $this->hasOne(Subject::class);
    }
    public function parents_detail()
    {
        return $this->hasOne(Parents_detail::class);
    }
    public function student_profile()
    {
        return $this->hasOne(Student_profile::class);
    }

    public function teacher_profile()
    {
        return $this->hasOne(Teacher_profile::class);
    }
}
