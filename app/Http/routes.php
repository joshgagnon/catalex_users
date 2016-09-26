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

Route::group(['middleware' => 'csrf'], function() {
    Route::get('/', 'HomeController@index');
    Route::get('/termsofuse', 'LegalController@termsofuse');
    Route::get('/customeragreement', 'LegalController@customeragreement');
    Route::get('/privacypolicy', 'LegalController@privacypolicy');

    Route::controllers([
        'auth' => 'Auth\AuthController',
        'password' => 'Auth\PasswordController',
        //'billing' => 'BillingController',
    ]);

    Route::group(['middleware' => 'auth'], function() {
        Route::get('/browser-login', ['as' => 'browser-login', 'uses' => 'HomeController@getBrowserLogin']);
        Route::get('/good-companies-login', ['as' => 'good-companies-login', 'uses' => 'HomeController@getGoodCompaniesLogin']);
        Route::controllers([
            'admin' => 'AdminController',
            'user' => 'UserController',
            'organisation' => 'OrganisationController',
            //'billing' => 'BillingController',
        ]);
    });



});


Route::post('oauth/access_token', function() {
    return Response::json(Authorizer::issueAccessToken());
});


Route::group(['prefix'=>'api', 'middleware' => 'oauth'], function() {
    Route::get('/user', 'UserController@info');
});


Route::get('login/law-browser', ['middleware' => ['check-authorization-params', 'csrf', 'auth'], function() {
    $params = Authorizer::getAuthCodeRequestParams();
    $params['user_id'] = Auth::user()->id;
    $redirectUri = Authorizer::issueAuthCode('user', $params['user_id'], $params);
    return Redirect::to($redirectUri);
}]);

Route::get('login/good-companies', ['middleware' => ['check-authorization-params', 'csrf', 'auth'], function() {
    $params = Authorizer::getAuthCodeRequestParams();
    $params['user_id'] = Auth::user()->id;
    $redirectUri = Authorizer::issueAuthCode('user', $params['user_id'], $params);
    return Redirect::to($redirectUri);
}]);

Route::post('mail/send', 'MailController@send');
Route::post('mail/view', 'MailController@view');
