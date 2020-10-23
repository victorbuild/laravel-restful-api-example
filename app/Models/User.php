<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use HasApiTokens;

    const ADMIN_USER = 'admin';
    const MEMBER_USER = 'member';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
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

    /**
     * 會員與動物資源的關聯
     */
    public function animals()
    {
        return $this->hasMany('App\Models\Animal', 'user_id', 'id');
    }

    /**
     * 是否為管理員（減少偶合使用）
     */
    public function isAdmin()
    {
        return $this->permission === User::ADMIN_USER;
    }
}
