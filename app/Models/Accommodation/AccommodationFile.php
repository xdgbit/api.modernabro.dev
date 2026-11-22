<?php

namespace App\Models\Accommodation;

use App\Http\Helpers\Classes\LFM;
use Illuminate\Database\Eloquent\Model;

class AccommodationFile extends Model
{
    protected $table = 'accommodation_files';
    protected $guarded = [];
    protected $appends = ['image_src'];

    public function getImageSrcAttribute()
    {
        $file_id = $this->file_id ? $this->file_id : 0;

        return LFM::downloadLink($file_id, '404.jpg');
    }
}
