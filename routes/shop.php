<?php

// The public shop: the cake builder, the cart and order tracking. Loaded from routes/web.php.

use App\Http\Controllers\Shop\CakeStudioController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\OrderTrackingController;
use Illuminate\Support\Facades\Route;

// Cake Studio: public
Route::get('/cake-studio', [CakeStudioController::class, 'index'])->name('cake-studio.index');

// Cart: public
Route::post('/cart/add', [CartController::class, 'store'])->name('cart.add');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::delete('/cart/{itemId}', [CartController::class, 'destroy'])->name('cart.remove');
Route::post('/cart/place-order', [CartController::class, 'placeOrder'])->name('cart.place-order');

// Order tracking: public
Route::get('/track-order', [OrderTrackingController::class, 'index'])->name('orders.track');
Route::post('/track-order', [OrderTrackingController::class, 'lookup'])->middleware('throttle:10,1')->name('orders.track.lookup');
Route::post('/orders/{orderId}/dismiss', [OrderTrackingController::class, 'dismiss'])->whereNumber('orderId')->name('orders.dismiss');
