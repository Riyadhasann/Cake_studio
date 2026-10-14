<?php

use App\Http\Controllers\Admin\CakePreviewController;
use App\Http\Controllers\Admin\CakeSizeController;
use App\Http\Controllers\Admin\CakeVisualController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\CakeStudioController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // Admin only: logged-in customers get a 403
    Route::middleware(EnsureUserIsAdmin::class)->group(function () {
        Route::redirect('/admin', '/admin/orders');

        Route::get('/admin/cake-previews', [CakePreviewController::class, 'index'])->name('admin.cake-previews.index');
        Route::post('/admin/cake-previews/{cakePreview}', [CakePreviewController::class, 'update'])->name('admin.cake-previews.update');

        Route::get('/admin/orders', [OrderController::class, 'index'])->name('admin.orders.index');
        Route::post('/admin/orders/{order}/deliver', [OrderController::class, 'deliver'])->name('admin.orders.deliver');
        Route::post('/admin/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('admin.orders.cancel');
        Route::delete('/admin/orders/{order}', [OrderController::class, 'destroy'])->name('admin.orders.destroy');

        Route::get('/admin/cake-images', [CakeVisualController::class, 'index'])->name('admin.cake-visuals.index');
        Route::post('/admin/cake-images/{type}/{id}', [CakeVisualController::class, 'update'])->whereIn('type', ['sponge', 'cream', 'part'])->whereNumber('id')->name('admin.cake-visuals.update');
        Route::delete('/admin/cake-images/{type}/{id}', [CakeVisualController::class, 'destroy'])->whereIn('type', ['sponge', 'cream', 'part'])->whereNumber('id')->name('admin.cake-visuals.destroy');
        Route::post('/admin/cake-images/{type}/{id}/default', [CakeVisualController::class, 'setDefault'])->whereIn('type', ['sponge', 'cream'])->whereNumber('id')->name('admin.cake-visuals.default');
        Route::post('/admin/cake-images/{type}/{id}/texture', [CakeVisualController::class, 'updateTexture'])->whereIn('type', ['sponge', 'cream'])->whereNumber('id')->name('admin.cake-visuals.texture.update');
        Route::delete('/admin/cake-images/{type}/{id}/texture', [CakeVisualController::class, 'destroyTexture'])->whereIn('type', ['sponge', 'cream'])->whereNumber('id')->name('admin.cake-visuals.texture.destroy');
        Route::post('/admin/cake-images/{type}/order', [CakeVisualController::class, 'reorder'])->whereIn('type', ['sponge', 'cream'])->name('admin.cake-visuals.reorder');

        Route::resource('/admin/sizes', CakeSizeController::class)
            ->only(['index', 'store', 'edit', 'update', 'destroy'])
            ->whereNumber('size')
            ->names('admin.sizes');

    });
});

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

require __DIR__.'/settings.php';
