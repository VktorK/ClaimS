<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('vue-app');
});

Route::get('/livewire-demo', function () {
    return view('livewire-demo');
})->name('livewire.demo');

Route::get('/jwt-demo', function () {
    return view('jwt-demo');
})->name('jwt.demo');

Route::get('/jwt-test', function () {
    return view('jwt-test');
})->name('jwt.test');

Route::get('/vue-app', function () {
    return view('vue-app');
})->name('vue.app');

Route::get('/auth-demo', function () {
    return view('auth-demo');
})->name('auth.demo');

// Маршрут для перенаправления неавторизованных пользователей
Route::get('/login', function () {
    return view('vue-app');
})->name('login');

// Fallback маршрут для SPA - все остальные маршруты должны возвращать vue-app
Route::get('{any}', function () {
    return view('vue-app');
})->where('any', '.*');
