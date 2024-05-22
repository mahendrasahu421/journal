<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\admin\ReviewerController;
use App\Http\Middleware\AdminAuth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/



Route::group(['middleware' => ['auth']], function () {
    /*Manage customer routes*/
});


Route::get('/', [LoginController::class, 'index']);
Route::post('login', [LoginController::class, 'store']);


Route::get('admin', [AdminController::class, 'index']);
Route::get('admin/add-new-reviewers', [ReviewerController::class, 'index']);
Route::get('admin/reviewers-list', [ReviewerController::class, 'index']);
