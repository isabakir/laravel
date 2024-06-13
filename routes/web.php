<?php

use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Trendyol\CategoryController;
use App\Models\Category;
use Illuminate\Support\Facades\Route;
ini_set('memory_limit', '256M');
Route::get('/', function () {
    return view('welcome');
});
Route::get('/categories', [CategoryController::class, 'getCategories']);
Route::get('/attributes', [CategoryController::class, 'getAttributes']);
Route::get("/create-feature",[ProductController::class,'createFeature']);
Route::get("/create-category",[ProductController::class,'createCategory']);
//addAttributesToCategory
Route::get("/add-attributes-to-category",[CategoryController::class,'addAttributesToCategory']);


/**listelme */
Route::get("/list-attribute",[ProductController::class,'listAttribute']);