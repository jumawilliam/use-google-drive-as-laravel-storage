<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GdriveController;
use App\Models\File;
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

Route::get('/', function () {
    $files=File::all();
    return view('welcome')->with('files',$files);
});

Route::resource('files',GdriveController::class);
