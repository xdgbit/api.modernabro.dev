<?php

namespace App\Models\User;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class UserExpert extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    use HasRoles;

    protected string $guard_name = 'user_expert';
    protected $table = 'user_experts';
    protected $fillable = [
        'name',
        'mobile',
        'email',
        'password',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'status'        => 'boolean',
        'last_login_at' => 'datetime',
    ];
    protected $appends = ['jalali_submitted_at'];

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getJalaliSubmittedAtAttribute()
    {
        return $this->submitted_at ? ConvertNumbersEntoFa(verta($this->submitted_at) . '') : null;
    }
}