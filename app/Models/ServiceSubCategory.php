<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceSubCategory extends Model
{
    protected $guarded=['id'];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function getImageAttribute($image)
    {
        $defaultImage = 'default_user.png';
        return asset('uploads/sub_category_images/' . ($image ?? $defaultImage));
    }
}
