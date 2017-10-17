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


Route::post('user/invite-user', 'UserController@createOrFindUser');
Route::post('api/user/invite-users', 'UserController@findOrCreateUsers');

Route::group(['middleware' => 'csrf'], function() {

    Route::get('/termsofuse', 'LegalController@termsofuse');
    Route::get('/privacypolicy', 'LegalController@privacypolicy');

    // Guest routes
    Route::group(['middleware' => 'guest'], function() {
        Route::get('/password/first-login/{token}', 'Auth\FirstLoginController@index')->name('first-login.index');
        Route::post('/password/first-login', 'Auth\FirstLoginController@setPassword')->name('first-login.set-password');
    });

    Route::controllers([
        'auth' => 'Auth\AuthController',
        'password' => 'Auth\PasswordController'
    ]);

    Route::get('/good-companies-login', ['as' => 'good-companies-login', 'uses' => 'HomeController@getGoodCompaniesLogin', 'middleware' => 'auth:gc']);

    // Authenticated routes
    Route::group(['middleware' => 'auth'], function() {
        /**
         * SSO routes
         */
        Route::get('/browser-login', ['as' => 'browser-login', 'uses' => 'HomeController@getBrowserLogin']);
        Route::get('/sign-login', ['as' => 'sign-login', 'uses' => 'HomeController@getSignLogin']);
        Route::get('/services', 'HomeController@index')->name('services');
        /**
         * Services routes
         */
        Route::get('my-services', 'SubscriptionController@index')->name('user-services.index');
        Route::post('my-services', 'SubscriptionController@update')->name('user-services.update');
        Route::get('my-services/return-from-billing', 'SubscriptionController@update')->name('user-services.return-from-billing');

        /**
         * Billing Routes
         */
        Route::get('billing/edit', 'BillingController@edit')->name('billing.edit');
        Route::post('billing/edit', 'BillingController@update')->name('billing.update');
        Route::delete('billing/delete', 'BillingController@delete')->name('billing.delete');

        Route::get('billing/retry', 'BillingController@retryBilling')->name('billing.retry');

        Route::get('billing', 'BillingController@index')->name('billing');
        Route::get('billing/invoices/{invoice}', 'InvoiceController@render')->name('invoices.view');
        Route::get('billing/invoices/{invoice}/download', 'InvoiceController@download')->name('invoices.download');
        Route::post('billing/invoices/{invoice}/resend', 'InvoiceController@resend')->name('invoices.resend');

        Route::get('billing/store-card', 'BillingController@storeCard')->name('billing.store-card');
        Route::get('billing/register-card', 'BillingController@createCard')->name('billing.register-card');
        Route::post('billing/register-card', 'BillingController@finishCreateCard')->name('billing.finish-create-card');

        /**
         * User routes
         */
        Route::get('/', 'HomeController@index')->name('index');
        Route::get('user/profile', 'UserController@getProfile')->name('user.profile');

        /**
         * Organisation routes
         */
        Route::get('/organisation', 'OrganisationController@index')->name('organisation.index');

        // Organisation invite routes
        Route::get('organisation-invites', 'OrganisationInviteController@index')->name('organisation-invites.index');
        Route::post('organisation-invites/{organisation_invite}/accept', 'OrganisationInviteController@accept')->name('organisation-invites.accept');
        Route::delete('organisation-invites/{organisation_invite}/', 'OrganisationInviteController@dismiss')->name('organisation-invites.delete');

        // Organisation member routes
        Route::post('organisation/{organisation_id}/leave', 'OrganisationMemberController@leave')->name('organisation.leave');

        // Organisation admin routes
        Route::post('organisation/users/{user}/remove', 'OrganisationAdminController@removeUser')->name('organisation.users.remove');

        /**
         * Invoice recipient routes
         */
        Route::get('organisation/invoice-recipients', 'InvoiceRecipientController@index')->name('invoice-recipients.index');
        Route::get('organisation/invoice-recipients/{recipientId}/edit', 'InvoiceRecipientController@edit')->name('invoice-recipients.edit');
        Route::put('organisation/invoice-recipients/{recipientId}', 'InvoiceRecipientController@update')->name('invoice-recipients.update');
        Route::delete('organisation/invoice-recipients/{recipient}', 'InvoiceRecipientController@delete')->name('invoice-recipients.delete');

        /**
         * Admin Routes
         */
        Route::post('impersonation/{user}', 'ImpersonationController@startImpersonation');
        Route::delete('impersonation', 'ImpersonationController@endImpersonation');

        Route::get('admin/billing/{billingDetail}', 'AdminController@billingOverview');

        Route::get('admin/stats', 'AdminController@stats')->name('admin.stats');

        Route::get('admin/users', 'AdminController@allUsers')->name('admin.users');

        /**
         * Email verification
         */
        Route::get('email-verification', 'EmailVerificationController@sendEmail')->name('email-verification.send-email');
        Route::get('email-verification/{token}', 'EmailVerificationController@verify')->name('email-verification.verify');

        /**
         * Controllers
         */
        Route::controllers([
            'admin' => 'AdminController',
            'user' => 'UserController',
            'organisation' => 'OrganisationController',
            'billing' => 'BillingController',
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

Route::get('login/sign', ['middleware' => ['check-authorization-params', 'csrf', 'auth'], function(\Illuminate\Http\Request $request) {
    $params = Authorizer::getAuthCodeRequestParams();
    $params['user_id'] = Auth::user()->id;
    $redirectUri = Authorizer::issueAuthCode('user', $params['user_id'], $params);

    if ($request->next) {
        $redirectUri = $redirectUri . '&next=' . $request->next;
    }

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
Route::post('mail/send-documents', 'MailController@sendDocuments');

Route::get('api/user/{user}', 'UserController@apiUserInfo');
