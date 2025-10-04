<?php

use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/', [App\Http\Controllers\PostController::class, 'index'])->name('posts.index');
Route::resource('posts', App\Http\Controllers\PostController::class);
