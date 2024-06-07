<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $table='categories';
    protected $fillable = ['name', 'slug', 'displayName', 'parent_id',"trendyol_id","cscart_id"];
    public function attributes()
    {
        return $this->hasMany(AttributeCategory::class);
    }
    /**category */

    //attribute_categories
    public function attributeCategories()
    {
        return $this->hasMany(AttributeCategory::class);
    }
}
