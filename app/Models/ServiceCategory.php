<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    protected $guarded=['id'];

    public function subcategories()
    {
        return $this->hasMany(ServiceSubCategory::class);
    }
}
