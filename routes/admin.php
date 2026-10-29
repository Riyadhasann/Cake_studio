<?php

// The admin pages: signed-in, verified admins only (logged-in customers get a 403). Loaded from routes/web.php.

use App\Http\Controllers\Admin\CakeDesignController;
use App\Http\Controllers\Admin\CakePhotoController;
use App\Http\Controllers\Admin\CakeSizeController;
use App\Http\Controllers\Admin\CakeVariantController;
use App\Http\Controllers\Admin\CakeVariantImageController;
use App\Http\Controllers\Admin\CakeVisualController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FlavorController;
use App\Http\Controllers\Admin\FlavorSurfaceController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\SiteSettingsController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', EnsureUserIsAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', DashboardController::class)->name('home');

    Route::get('settings', [SiteSettingsController::class, 'index'])->name('settings.index');
    Route::put('settings', [SiteSettingsController::class, 'update'])->name('settings.update');
    Route::put('settings/scene', [SiteSettingsController::class, 'updateScene'])->name('settings.scene');
    Route::post('settings/images/{key}', [SiteSettingsController::class, 'updateImage'])->whereIn('key', ['logo', 'favicon', 'banner', 'scene'])->name('settings.images.update');
    Route::delete('settings/images/{key}', [SiteSettingsController::class, 'destroyImage'])->whereIn('key', ['logo', 'favicon', 'banner', 'scene'])->name('settings.images.destroy');

    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('orders/{order}/deliver', [OrderController::class, 'deliver'])->name('orders.deliver');
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::delete('orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');

    Route::get('cake-images', [CakeVisualController::class, 'index'])->name('cake-visuals.index');
    Route::post('cake-images/{type}/{id}', [CakeVisualController::class, 'update'])->whereIn('type', ['sponge', 'cream', 'part'])->whereNumber('id')->name('cake-visuals.update');
    Route::delete('cake-images/{type}/{id}', [CakeVisualController::class, 'destroy'])->whereIn('type', ['sponge', 'cream', 'part'])->whereNumber('id')->name('cake-visuals.destroy');
    Route::post('cake-images/{type}/{id}/default', [CakeVisualController::class, 'setDefault'])->whereIn('type', ['sponge', 'cream'])->whereNumber('id')->name('cake-visuals.default');
    Route::post('cake-images/photo', [CakePhotoController::class, 'update'])->name('cake-photo.update');
    Route::delete('cake-images/photo', [CakePhotoController::class, 'destroy'])->name('cake-photo.destroy');
    Route::post('flavors/{type}', [FlavorController::class, 'store'])->whereIn('type', ['sponge', 'cream'])->name('flavors.store');
    Route::put('flavors/{type}/{id}', [FlavorController::class, 'update'])->whereIn('type', ['sponge', 'cream'])->whereNumber('id')->name('flavors.update');
    Route::delete('flavors/{type}/{id}', [FlavorController::class, 'destroy'])->whereIn('type', ['sponge', 'cream'])->whereNumber('id')->name('flavors.destroy');
    Route::post('cake-images/{type}/order', [CakeVisualController::class, 'reorder'])->whereIn('type', ['sponge', 'cream'])->name('cake-visuals.reorder');
    Route::get('designs', [CakeDesignController::class, 'index'])->name('designs.index');
    Route::post('designs', [CakeDesignController::class, 'store'])->name('designs.store');
    Route::put('designs/{design}', [CakeDesignController::class, 'update'])->whereNumber('design')->name('designs.update');
    Route::put('designs/{design}/fit', [CakeDesignController::class, 'updateFit'])->whereNumber('design')->name('designs.fit');
    Route::post('designs/{design}/fit-from-photo', [CakeDesignController::class, 'fitFromPhoto'])->whereNumber('design')->name('designs.fit-from-photo');
    Route::delete('designs/{design}/toppings/{index}', [CakeDesignController::class, 'destroyTopping'])->whereNumber(['design', 'index'])->name('designs.toppings.destroy');
    Route::delete('designs/{design}', [CakeDesignController::class, 'destroy'])->whereNumber('design')->name('designs.destroy');
    Route::post('designs/{design}/frames', [CakeDesignController::class, 'storeFrames'])->whereNumber('design')->name('designs.frames.store');
    Route::delete('designs/{design}/frames/{frame}', [CakeDesignController::class, 'destroyFrame'])->whereNumber(['design', 'frame'])->name('designs.frames.destroy');
    Route::post('cake-images/{type}/{id}/3d', [FlavorSurfaceController::class, 'update'])->whereIn('type', ['sponge', 'cream'])->whereNumber('id')->name('cake-visuals.surface.update');
    Route::delete('cake-images/{type}/{id}/3d', [FlavorSurfaceController::class, 'destroy'])->whereIn('type', ['sponge', 'cream'])->whereNumber('id')->name('cake-visuals.surface.destroy');

    Route::resource('sizes', CakeSizeController::class)
        ->only(['index', 'store', 'edit', 'update', 'destroy'])
        ->whereNumber('size')
        ->names('sizes');

    Route::get('cake-variants', [CakeVariantController::class, 'index'])->name('cake-variants.index');
    Route::post('cake-variants', [CakeVariantController::class, 'store'])->name('cake-variants.store');
    Route::get('cake-variants/{variant}/edit', [CakeVariantController::class, 'edit'])->whereNumber('variant')->name('cake-variants.edit');
    Route::delete('cake-variants/{variant}', [CakeVariantController::class, 'destroy'])->whereNumber('variant')->name('cake-variants.destroy');
    Route::put('cake-variants/{variant}', [CakeVariantController::class, 'update'])->whereNumber('variant')->name('cake-variants.update');
    Route::post('cake-variants/{variant}/publish', [CakeVariantController::class, 'publish'])->whereNumber('variant')->name('cake-variants.publish');
    Route::delete('cake-variants/{variant}/publish', [CakeVariantController::class, 'unpublish'])->whereNumber('variant')->name('cake-variants.unpublish');
    Route::post('cake-variants/{variant}/base', [CakeVariantImageController::class, 'updateBase'])->whereNumber('variant')->name('cake-variants.base.update');
    Route::delete('cake-variants/{variant}/base', [CakeVariantImageController::class, 'destroyBase'])->whereNumber('variant')->name('cake-variants.base.destroy');
    Route::post('cake-variants/{variant}/shading', [CakeVariantImageController::class, 'updateShading'])->whereNumber('variant')->name('cake-variants.shading.update');
    Route::delete('cake-variants/{variant}/shading', [CakeVariantImageController::class, 'destroyShading'])->whereNumber('variant')->name('cake-variants.shading.destroy');
    Route::post('cake-variants/{variant}/photos', [CakeVariantImageController::class, 'storePhoto'])->whereNumber('variant')->name('cake-variants.photos.store');
    Route::delete('cake-variants/{variant}/photos/{photo}', [CakeVariantImageController::class, 'destroyPhoto'])->whereNumber(['variant', 'photo'])->name('cake-variants.photos.destroy');
});
