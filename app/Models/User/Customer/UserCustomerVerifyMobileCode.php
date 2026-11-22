<?php

namespace App\Models\User\Customer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserCustomerVerifyMobileCode extends Model
{
    use SoftDeletes;

    protected $table = 'user_customer_verify_mobile_codes';
    protected $dates = ['created_at', 'updated_at', 'deleted_at', 'archived_at', 'expired_at', 'success_attempt_at'];
    protected $guarded = [];
    protected $casts = [
        'expired_at'         => 'datetime',
        'success_attempt_at' => 'datetime',
        'archived_at'        => 'datetime',
        'deleted_at'         => 'datetime',
    ];

    public function user_customer()
    {
        return $this->belongsTo(UserCustomer::class, 'user_customer_id', 'id');
    }

    public function scopeNotArchive($q)
    {
        return $q->whereNull('archived_at');
    }

    public function scopeActive($q)
    {
        return $q->where(function ($q) {
            return $q->notArchive()->whereNull('success_attempt_at');
        });
    }

    public function scopeLast($q)
    {
        return $q->orderBy('id', 'Desc');
    }
}
