<?php

namespace App\Models\LFM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class FileMimeType extends Model
{
    use SoftDeletes;

    protected $table = 'lfm_file_mime_types';

}