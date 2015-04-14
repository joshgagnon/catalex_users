<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'HomeController@index');
Route::get('/browser-login', ['as' => 'browser-login', 'uses' => 'HomeController@getBrowserLogin']);
Route::get('/termsofuse', 'LegalController@termsofuse');
Route::get('/customeragreement', 'LegalController@customeragreement');
Route::get('/privacypolicy', 'LegalController@privacypolicy');

// TODO: Remove
Route::get('/send-welcome', ['as' => 'send-welcome', 'uses' => 'HomeController@getSendWelcome']);
Route::get('/email', function() {
    return view('emails.welcome', ['title' => 'Welcome', 'name' => 'Your Name']);
});

Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
    'admin' => 'AdminController',
    'user' => 'UserController',
	'organisation' => 'OrganisationController',
]);

