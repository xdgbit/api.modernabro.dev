<?php

namespace App\Models\Sys\PaymentMethod;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SysPaymentMethodWithIdDefault extends Model
{
    use SoftDeletes;

    protected $table = 'sys_payment_method_with_id_default';

    public function sys_payment_method()
    {
        return $this->belongsTo(\App\Models\Sys\PaymentMethod\SysPaymentMethod::class, 'sys_payment_method_id', 'id');
    }

    public function user_expert_editor()
    {
        return $this->belongsTo(\App\Models\User\Expert\UserExpert::class, 'edited_by', 'id');
    }

    public function scopeNotArchive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeLast($query)
    {
        return $query->orderBy('id', 'desc');
    }

    public function scopeTypeSell($query)
    {
        return $query->where('type', 'sell');
    }

    public function scopeTypeBuy($query)
    {
        return $query->where('type', 'buy');
    }

    public function getEditorFullNameAttribute()
    {
        if ($this->user_expert_editor)
        {
            return $this->user_expert_editor->first_name . ' ' . $this->user_expert_editor->last_name;
        }
        else
        {
            return '';
        }
    }

    public function getJalaliEditedAtAttribute()
    {
        return $this->edited_at ? ConvertNumbersEntoFa(verta($this->edited_at) . '') : null;
    }
}
