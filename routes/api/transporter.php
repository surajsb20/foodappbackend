<?php

//Login
Route::post('/login', 'TransporterAuth\LoginController@login');
Route::post('/register', 'TransporterAuth\RegisterController@apiRegister');
Route::post('/verify/otp', 'TransporterAuth\LoginController@UserLogin');

Route::group(['middleware' => ['auth:transporterapi']], function () {

    Route::group(['prefix' => 'profile'], function () {
        Route::get('/', 'TransporterResource\ProfileController@index');
        Route::post('/', 'TransporterResource\ProfileController@update');

        Route::post('/password', 'TransporterResource\ProfileController@password');
        Route::post('/location', 'TransporterResource\ProfileController@location');
    });
    Route::get('/logout', 'TransporterResource\ProfileController@logout');
    Route::get('/vehicles', 'TransporterResource\ShiftResource@vehicles');

    Route::resource('order', 'TransporterResource\OrderResource');
    Route::get('history', 'TransporterResource\OrderResource@history');
    Route::resource('shift', 'TransporterResource\ShiftResource');
    Route::get('earning', 'TransporterResource\EarningController@index');
    Route::get('status', 'TransporterResource\EarningController@checkStatus');
    Route::resource('shift/timing', 'TransporterResource\ShifttimingResource');
    Route::resource('dispute', 'Resource\DisputeResource');
    Route::post('/rating', 'TransporterResource\OrderResource@rate_review');
    Route::get('/notice', 'Resource\NoticeBoardResource@TransporterNotice');
    Route::get('/disputehelp', 'Resource\DisputeHelpResource@index');
    Route::post('/request/order', 'TransporterResource\OrderResource@providerRequest');


    Route::get('documents', 'TransporterResource\DocumentController@index');
    Route::post('documents/{id}', 'TransporterResource\DocumentController@uploadDocument');
    Route::get('documents/status', 'TransporterResource\DocumentController@checkSubmittedDocuments');


});