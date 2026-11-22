<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactMessage extends Model
{
    use SoftDeletes;

    protected $appends = [
        'jalali_submitted_at'
    ];
    protected $fillable = [
        'mobile',
        'subject',
        'message',
        'is_read',
        'full_name',
        'submitted_at',
    ];

    public function getJalaliSubmittedAtAttribute()
    {
        return $this->submitted_at ? ConvertNumbersEntoFa(verta($this->submitted_at) . '') : null;
    }
}
