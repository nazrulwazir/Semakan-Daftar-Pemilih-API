<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::as(config('api.as'))
    ->prefix(config('api.pre'))
    ->group(base_path('routes/api/' . config('api.route_file') . '.php'));