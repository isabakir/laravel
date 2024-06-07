<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributeCategory extends Model
{
    use HasFactory;
    protected $table='attribute_categories';
    protected $fillable = ['attribute_id',"category_id"];
    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
    public function category()
    {
        return $this->hasMany(Category::class,"id",'category_id');
    }

}