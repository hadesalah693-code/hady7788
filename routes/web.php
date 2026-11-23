<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PropertyImageController;
use App\Http\Controllers\ServiceRequestImageController;
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
use App\Livewire\Services\MyServiceRequests;
use App\Livewire\Services\ProviderProfile;
use App\Livewire\Services\ProviderRequests;
use App\Livewire\Services\ServiceCatalog;
use App\Livewire\Services\ServiceRequestDetails;
use App\Livewire\Services\ServiceRequestForm;

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

Route::get('/property-images/{image}', PropertyImageController::class)
    ->whereNumber('image')
    ->name('properties.images.show');

Route::get('/services', ServiceCatalog::class)->name('services.index');

Route::get('/service-request-images/{image}', ServiceRequestImageController::class)
    ->whereNumber('image')
    ->name('service-request-images.show');

Route::middleware('auth')->group(function () {
    Route::get('/services/request', ServiceRequestForm::class)->name('services.request');
});

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

    Route::get('/account/service-requests', MyServiceRequests::class)->name('account.service-requests.index');
    Route::get('/account/service-requests/{serviceRequest}', ServiceRequestDetails::class)->name('account.service-requests.show');

    Route::get('/account/service-provider', ProviderProfile::class)->name('account.service-provider');
    Route::get('/account/service-provider/requests', ProviderRequests::class)->name('account.service-provider.requests');
});
