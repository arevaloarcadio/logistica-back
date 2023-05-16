<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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




Route::group(['middleware' => ['jwt']], function() {
	Route::namespace('App\Http\Controllers')->group(static function() {
	    
	    Route::prefix('type_shipments')->group(function () {
	   		Route::get('/', 'TypeShipmentController@index');
	   		Route::post('/', 'TypeShipmentController@store');
	   		Route::post('/{id}', 'TypeShipmentController@update');
		});

	    Route::prefix('shipments')->group(function () {
	   		Route::get('/', 'ShipmentController@index');
	   		Route::get('/my_shipments', 'ShipmentController@myShipments');
			Route::post('/', 'ShipmentController@store');
	   		Route::post('/search', 'ShipmentController@search');
	   		Route::post('/search/my_shipments', 'ShipmentController@searchMyShipments');
		});

	    Route::prefix('users')->group(function () {
	    	Route::get('/', 'UserController@index');
	   		Route::get('/drivers', 'UserController@getDrivers');
	   		Route::post('/', 'UserController@store');
	   		Route::post('/{id}', 'UserController@update');
	   		Route::put('/{id}', 'UserController@active');
	   		Route::delete('/{id}', 'UserController@destroy');
		});
   		
   		Route::post('/logout', 'AuthController@logout');
    	Route::post('/refresh', 'AuthController@refresh');
    	Route::get('/me', 'AuthController@me');
	
	});
});

Route::namespace('App\Http\Controllers')->group(static function() {
	Route::post('/login', 'AuthController@login');
});
