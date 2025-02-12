<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    protected $guarded=['id'];

    public function subcategories()
    {
        return $this->hasMany(ServiceSubCategory::class, 'service_category_id');
    }
    public function getIconAttribute($icon)
    {
        $defaultIcon = 'default_user.png';
        return asset('uploads/category_icons/' . ($icon ?? $defaultIcon));
    }
}
