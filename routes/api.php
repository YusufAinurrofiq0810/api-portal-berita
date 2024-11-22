<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'forceJsonResponse'], function () {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::group(['prefix' => 'post'], function () {
            Route::get('/', [PostController::class, 'getAll'])->name('getAll');
            Route::get('/get-all', [PostController::class, 'getAll'])->name('getAllPublic')->withoutMiddleware('auth:sanctum');
            Route::get('/{id}', [PostController::class, 'getOne'])->name('getOne')->withoutMiddleware('auth:sanctum');
            Route::post('/', [PostController::class, 'create'])->name('create');
            Route::patch('/{id}', [PostController::class, 'update'])->name('update');
            Route::delete('/{id}', [PostController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'comment'], function () {
            Route::get('/user', [CommentController::class, 'getAllByUser'])->name('getAllByUser');
            Route::get('/{postId}', [CommentController::class, 'getAllByPost'])->name('getAllByPost')->withoutMiddleware('auth:sanctum');
            Route::post('/{postId}', [CommentController::class, 'create'])->name('create');
            Route::patch('/{commentId}', [CommentController::class, 'update'])->name('update');
            Route::delete('/{commentId}', [CommentController::class, 'delete'])->name('delete');
        });
    });
});
