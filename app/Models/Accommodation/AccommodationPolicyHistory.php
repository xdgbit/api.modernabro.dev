<?php

namespace App\Models\Accommodation;

use App\Models\User\UserExpert;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccommodationPolicyHistory extends Model
{
    use SoftDeletes;

    protected $table = 'accommodation_policy_histories';
    protected $guarded = [];

    public function submitter(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(UserExpert::class, 'submitted_by', 'id');
    }

    public function editor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(UserExpert::class, 'edited_by', 'id');
    }

    public function accommodation(): HasOne
    {
        return $this->belongsTo(Accommodation::class);
    }

    public function getSubmitterFullNameAttribute()
    {
        $submitter = $this->submitter;
        if ($submitter)
        {
            return $submitter->full_name;
        }
        else
        {
            return null;
        }
    }

    public function getEditorFullNameAttribute()
    {
        $editor = $this->editor;
        if ($editor)
        {
            return $editor->full_name;
        }
        else
        {
            return null;
        }
    }

    public function getJalaliSubmittedAtAttribute()
    {
        return $this->submitted_at ? ConvertNumbersEntoFa(verta($this->submitted_at) . '') : null;
    }

    public function getJalaliEditedAtAttribute()
    {
        return $this->edited_at ? ConvertNumbersEntoFa(verta($this->edited_at) . '') : null;
    }
}