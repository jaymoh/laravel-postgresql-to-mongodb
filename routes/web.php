<?php

use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/', [App\Http\Controllers\PostController::class, 'index'])->name('posts.home');
Route::resource('posts', App\Http\Controllers\PostController::class);
Route::resource('users', App\Http\Controllers\UserController::class)->only(['index', 'show']);
Route::post('comments', [App\Http\Controllers\CommentController::class, 'store'])->name('comments.store');
Route::delete('posts/{postId}/comments/{commentIndex}', [App\Http\Controllers\CommentController::class, 'destroy'])->name('comments.destroy');
// SearchController
