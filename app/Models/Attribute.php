<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    use HasFactory;
    protected $table='attributes';
    protected $fillable = ['name', 'slug', 'displayName', "category_id","required","varianter","slicer",'parent_id',"trendyol_id"];
    //category
    public function category()
    {
        return $this->hasMany(Category::class,"id",'category_id');
    }
    //attributeValues
    public function attributeValues()
    {
        return $this->hasMany(AttributeValue::class);
    }
    //attribute_categories
    public function attributeCategories()
    {
        return $this->hasMany(AttributeCategory::class);
    }
}