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

    //when delete a category related subcategory also deleted
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($category) {
            $category->subcategories()->delete(); // Deletes all associated subcategories
        });
    }
}
