<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceSubCategory extends Model
{
    protected $guarded = ['id'];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }
    public function services()
    {
        return $this->belongsTo(Services::class, 'service_id');
    }

    public function getImageAttribute($image)
    {
        $defaultImage = 'default_user.png';
        return asset('uploads/sub_category_images/' . ($image ?? $defaultImage));
    }
}
