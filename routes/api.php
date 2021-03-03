<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LocationController;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Endpoint for location search by post code
Route::get('location/{number}', [LocationController::class, 'getLocationDataByPostNumber'])
->where('number', '\d{3}-\d{4}');

// Endpoint for weather search by coordinates
Route::get('location/weather/{latitude}/{longitude}', [LocationController::class, 'getWeatherData'])
->where(['latitude' => '^[-]?([1-8]?\d(\.\d+)?|90(\.0+)?)', 'longitude' => '[-]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$']);

// Endpoint for weather search by coordinates
Route::get('location/restaurants/{latitude}/{longitude}', [LocationController::class, 'getNearbyRestaurants'])
->where(['latitude' => '^[-]?([1-8]?\d(\.\d+)?|90(\.0+)?)', 'longitude' => '[-]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$']);

Route::get('location/google/{id}', [LocationController::class, 'getPlaceInformation']);


