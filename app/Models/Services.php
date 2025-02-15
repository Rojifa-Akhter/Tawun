<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Services extends Model
{
    protected $guarded = ['id'];

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }
    public function subCategory()
    {
        return $this->belongsTo(ServiceSubCategory::class, 'service_sub_categories_id');
    }

    public function getImageAttribute($image)
    {
        $defaultImage = 'default_user.png';
        return asset('uploads/service_images/' . ($image ?? $defaultImage));
    }
}
