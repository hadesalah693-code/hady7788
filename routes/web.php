<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PropertyImageController;
use App\Http\Controllers\PropertyInquiryController;
use App\Livewire\Account\BrokerDetails;
use App\Livewire\Account\CompanyDetails;
use App\Livewire\Account\Dashboard;
use App\Livewire\Account\Profile;
use App\Livewire\Account\Security;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Properties\CreateProperty;
use App\Livewire\Properties\EditProperty;
use App\Livewire\Properties\MyProperties;
use App\Livewire\Properties\PropertyList;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Public browsing, authentication and the authenticated account area.
| Account routes never accept a user identifier; every page works on the
| currently authenticated user only.
|
*/

Route::get('/', HomeController::class)->name('home');

Route::get('/properties', PropertyList::class)->name('properties.index');

Route::get('/properties/{property:slug}', [PropertyController::class, 'show'])->name('properties.show');

Route::post('/properties/{property:slug}/inquiries', [PropertyInquiryController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('properties.inquiries.store');

Route::get('/property-images/{image}', PropertyImageController::class)
    ->whereNumber('image')
    ->name('properties.images.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
});

Route::post('/logout', LogoutController::class)->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/account', Dashboard::class)->name('account.dashboard');
    Route::get('/account/profile', Profile::class)->name('account.profile');
    Route::get('/account/security', Security::class)->name('account.security');
    Route::get('/account/company', CompanyDetails::class)->name('account.company');
    Route::get('/account/broker', BrokerDetails::class)->name('account.broker');

    Route::get('/account/properties', MyProperties::class)->name('account.properties.index');
    Route::get('/account/properties/create', CreateProperty::class)->name('account.properties.create');
    Route::get('/account/properties/{property}', EditProperty::class)->name('account.properties.edit');
});
