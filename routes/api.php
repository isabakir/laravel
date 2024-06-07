<?php

use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Trendyol\CategoryController;
use App\Http\Controllers\User\UserController;


use Illuminate\Support\Facades\Route;


set_time_limit(0);
Route::get('/test', function () {
    return response()->json(['message' => 'Hello World!']);
});
Route::get("/product-list", [CategoryController::class, 'getProducts']);
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/profilim', [UserController::class, 'index']);


});

Route::prefix("product")->group(function(){
    Route::get('/list', [ProductController::class, 'index']);
    Route::get('/detail/{id}', [ProductController::class, 'detail']);
    Route::post('/create', [ProductController::class, 'create']);
    Route::post('/update/{id}', [ProductController::class, 'update']);
    Route::post('/delete/{id}', [ProductController::class, 'delete']);
});

Route::get("/varitans", [ProductController::class, 'listVariants']);

Route::prefix("user")->group(function () {
    /** giriş yapmıs kullanıcı  */

    /**ilk kayıt adımları */
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/create', [UserController::class, 'create']);
    /** sms code oluşturmak için */
    Route::post('/createSmsCode', [UserController::class, 'createPhoneCode']);
    Route::post('/enterSmsCode', [UserController::class, 'enterPhoneCode']);
});