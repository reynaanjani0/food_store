<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Customer;
use App\Http\Controllers\Api\Public;

// ROUTE GROUP CUSTOMER
Route::group(['prefix' => 'customer'], function () {

    /**
     * route "/register"
     * @method "POST"
     */
    Route::post('/register', Customer\RegisterController::class)
        ->name('customer.register');

    /**
     * route "/login"
     * @method "POST"
     */
    Route::post('/login', Customer\LoginController::class)
        ->name('customer.login');

    /**
     * route "/logout"
     * @method "POST"
     */
    Route::post('/logout', Customer\LogoutController::class)
        ->name('customer.logout');

    /**
     * route "/my-orders"
     * @method "GET"
     */
    Route::get('/my-orders', [Customer\MyOrderController::class, 'index'])
        ->name('customer.my-orders.index');

    /**
     * route "/my-orders/{snap_token}"
     * @method "GET"
     */
    Route::get('/my-orders/{snap_token}', [Customer\MyOrderController::class, 'show'])
        ->name('customer.my-orders.show');

    /**
     * route "/profile"
     * @method "GET"
     */
    Route::get('/profile', [Customer\MyProfileController::class, 'index'])
        ->name('customer.profile.index');

    /**
     * route "/profile"
     * @method "POST"
     */
    Route::post('/profile', [Customer\MyProfileController::class, 'update'])
        ->name('customer.profile.update');

    /**
     * route "/ratings"
     * @method "POST"
     */
    Route::post('/ratings', Customer\RatingController::class)
        ->name('customer.ratings');

});


// ROUTE GROUP PUBLIC
Route::group(['prefix' => 'public'], function () {

    /**
     * route "/sliders"
     * @method "GET"
     */
    Route::get('/sliders', Public\SliderController::class)
        ->name('public.sliders');

    /**
     * route "/categories"
     * @method "GET"
     */
    Route::get('/categories', [Public\CategoryController::class, 'index'])
        ->name('public.categories');

    /**
     * route "/categories/{slug}"
     * @method "GET"
     */
    Route::get('/categories/{slug}', [Public\CategoryController::class, 'show'])
        ->name('public.categories.show');

    /**
     * route "/products"
     * @method "GET"
     */
    Route::get('/products', [Public\ProductController::class, 'index'])
        ->name('public.products');

    /**
     * route "/products/open"
     * @method "GET"
     */
    Route::get('/products/open', [Public\ProductController::class, 'productOpen'])
        ->name('public.products.open');

        /**
 * route "/products/popular"
 * @method "GET"
 */
Route::get('/products/popular', [Public\ProductController::class, 'productPopular'])
    ->name('public.products.popular');

    /**
     * route "/products/{slug}"
     * @method "GET"
     */
    Route::get('/products/{slug}', [Public\ProductController::class, 'show'])
        ->name('public.products.show');

    /**
     * route "/carts"
     * @method "GET"
     */
    Route::get('/carts', [Public\CartController::class, 'index'])
        ->name('public.carts');

    /**
     * route "/carts"
     * @method "POST"
     */
    Route::post('/carts', [Public\CartController::class, 'store'])
        ->name('public.carts.store');

    /**
     * route "/carts/increment"
     * @method "POST"
     */
    Route::post('/carts/increment', [Public\CartController::class, 'IncrementCart'])
        ->name('public.carts.increment');

    /**
     * route "/carts/decrement"
     * @method "POST"
     */
    Route::post('/carts/decrement', [Public\CartController::class, 'DecrementCart'])
        ->name('public.carts.decrement');

    /**
     * route "/carts/destroy"
     * @method "DELETE"
     */
    Route::delete('/carts/{id}', [Public\CartController::class, 'destroy'])
        ->name('public.carts.destroy');

     /**
     * route "/search-destination"
     * @method "GET"
     */
    Route::get('/search-destination', [
        Public\RajaOngkirController::class,
        'searchDestination'
    ])->name('public.search-destination');

    /**
     * route "/check-ongkir"
     * @method "POST"
     */
    Route::post('/check-ongkir', [
        Public\RajaOngkirController::class,
        'checkOngkir'
    ])->name('public.ongkir');

     /**
     * route "/checkout"
     * @method "POST"
     */
    Route::post('/checkout', [Public\CheckoutController::class, 'store'])
        ->name('public.checkout');

     /**
     * route "/callback"
     * @method "POST"
     */
    Route::post('/callback', Public\CallbackController::class)
        ->name('public.callback');



});