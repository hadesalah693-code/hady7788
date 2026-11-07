<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PropertyImageController;
use App\Http\Controllers\PropertyInquiryController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\ServiceWorkflowController;
use App\Http\Controllers\ServiceRequestImageController;
use App\Http\Controllers\ServiceTechnicianController;
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

Route::get('/', HomeController::class)->name('home');
Route::get('/properties', PropertyList::class)->name('properties.index');
Route::get('/properties/{property:slug}', [PropertyController::class, 'show'])->name('properties.show');
Route::post('/properties/{property:slug}/inquiries', [PropertyInquiryController::class, 'store'])->middleware('throttle:5,1')->name('properties.inquiries.store');
Route::get('/property-images/{image}', PropertyImageController::class)->whereNumber('image')->name('properties.images.show');

Route::get('/home-services', [ServiceTechnicianController::class, 'index'])->name('services.index');
Route::get('/home-services/technicians/{technician}', [ServiceTechnicianController::class, 'show'])->whereNumber('technician')->name('services.technicians.show');

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

    Route::get('/account/home-services/requests/{serviceRequest}', [ServiceWorkflowController::class, 'show'])->whereNumber('serviceRequest')->name('services.requests.show');
    Route::post('/account/home-services/requests/{serviceRequest}/quotes', [ServiceWorkflowController::class, 'quote'])->whereNumber('serviceRequest')->middleware('throttle:10,1')->name('services.quotes.store');
    Route::post('/account/home-services/requests/{serviceRequest}/quotes/{quote}/accept', [ServiceWorkflowController::class, 'acceptQuote'])->whereNumber('serviceRequest')->whereNumber('quote')->name('services.quotes.accept');
    Route::post('/account/home-services/requests/{serviceRequest}/schedule', [ServiceWorkflowController::class, 'schedule'])->whereNumber('serviceRequest')->name('services.requests.schedule');
    Route::post('/account/home-services/requests/{serviceRequest}/complete', [ServiceWorkflowController::class, 'complete'])->whereNumber('serviceRequest')->name('services.requests.complete');
    Route::post('/account/home-services/requests/{serviceRequest}/review', [ServiceWorkflowController::class, 'review'])->whereNumber('serviceRequest')->name('services.reviews.store');
    Route::get('/account/home-services/images/{image}', ServiceRequestImageController::class)->whereNumber('image')->name('services.images.show');

    Route::get('/account/home-services/apply', [ServiceRequestController::class, 'applyForm'])->name('services.apply');
    Route::post('/account/home-services/apply', [ServiceRequestController::class, 'apply'])->middleware('throttle:3,1')->name('services.apply.store');
    Route::get('/account/home-services/requests/create', [ServiceRequestController::class, 'create'])->name('services.requests.create');
    Route::post('/account/home-services/requests', [ServiceRequestController::class, 'store'])->middleware('throttle:5,1')->name('services.requests.store');
});
