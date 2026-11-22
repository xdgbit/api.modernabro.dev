<?php

namespace App\Models\Product;

use App\Http\Helpers\Classes\LFM;
use Illuminate\Database\Eloquent\Model;

class ProductFile extends Model
{
    protected $table = 'product_files';
    protected $guarded = [];
    protected $appends = ['image_src'];

    public function getImageSrcAttribute()
    {
        $file_id = $this->file_id ? $this->file_id : 0;

        return LFM::downloadLink($file_id, '404.jpg');
    }
}
