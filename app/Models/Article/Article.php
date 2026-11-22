<?php

namespace App\Models\Article;

use App\Http\Helpers\Classes\LFM;
use App\Models\User\UserExpert;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use function ConvertNumbersEntoFa;

class Article extends Model
{
    use SoftDeletes;

    protected $table = 'articles';
    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function submitter()
    {
        return $this->belongsTo(UserExpert::class, 'submitted_by', 'id');
    }

    public function category()
    {
        return $this->belongsTo(ArticleCategory::class);
    }

    public function getCategoryTitleAttribute()
    {
        if ($this->category)
        {
            return $this->category->title;
        }
        else
        {
            return '';
        }
    }

    public function getSubmitterFullNameAttribute()
    {
        if ($this->submitter)
        {
            return $this->submitter->full_name;
        }
        else
        {
            return 'سیستمی';
        }
    }

    public function getJalaliSubmittedAtAttribute()
    {
        return $this->submitted_at ? ConvertNumbersEntoFa(verta($this->submitted_at) . '') : null;
    }

    public function getJalaliPublishedAtAttribute()
    {
        return $this->published_at ? ConvertNumbersEntoFa(verta($this->published_at) . '') : null;
    }

    public function getMainImageAttribute()
    {
        $file_id = $this->image_id ? $this->image_id : 0;

        return LFM::downloadLink($file_id, '404.jpg');
    }

    public function scopePublished($query)
    {
        return $query->where('published_at', '<=', Carbon::now());
    }
    public function scopeVisible($query)
    {
        return $query
            ->where('published_at', '<=', Carbon::now())
            ->where('is_active', true);
    }
}