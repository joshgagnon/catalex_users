# CataLex User Management Portal

[![Build Status](https://travis-ci.org/joshgagnon/catalex_users.svg?branch=master)](https://travis-ci.org/joshgagnon/catalex_users)

This app provides users, organisations and global admins an interface to manage their own (and others) user and billing details. It also provides login support for the Law Browser.

## Deployment

### Local Dev Copy

1. Install composer `curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/bin`
1. Copy `.env.example` to `.env` and fill in the blanks
1. Create a new database called `catalex_users`
1. Use composer to install PHP dependencies `composer install`
1. Install JS dependencies `npm install`
1. Install gulp globally `npm install -g gulp`
1. Migrate the DB`php artisan migrate`
1. Seed the DB`php artisan db:seed --seeder=DevelopmentSeeder`


## Tests

`./vendor/bin/phpunit`

### Options

**Log testing stats:** `--log-junit=log_name.xml`

**Run specific test:** `--filter=test_class_name_or_test_name`

## Deploying Live

To perform the initial deployment on a live server, clone the https://github.com/joshgagnon/catalex_utils.git repo. Edit the variables at the top of the install\_users.sh script then run it as root.

### OAuth

#### OAuth for logging into other CataLex services

For each service that needs to authenticate with CataLex, add a client and an endpoint for that client (example below).

The `--name="whatever"` is important—we match on it to find the right oauth_client record. For Good Companies the name is "Good Companies", for Law Browser the name is "Law Browser", and for sign the name is "Sign".

`php artisan oauth:add-client --client_id=gc --secret=gc --name="Good Companies"`

`php artisan oauth:add-endpoint --client_id=gc --endpoint=http://localhost:5667/auth/catalex/login`

#### OAuth for logging into CataLex Users

Currently the only OAuth provider setup is LinkedIn. For LinkedIn OAuth to work, it's ket and secret need to be entered into a new file `config/oauth.php`. Use `config/oauth.example.php` as an example.

### Updating Live

Live installations can be updated with the following command:

    sudo ./update.sh www-data

It must be run as root and provide the webserver username to avoid file permission errors. It will pull updates from git, apply migrations and update dependencies, so the application may be in the 'down' status for a few minutes.

## Development

### Generic Functionality

To add non-model specific, non-controller functionality to the app, the best place is `app/Library`. Functionality that won't need to be mocked for testing should be made as a static function directly accessible on in a library class. Testable functions should not be static, even if they require no state - instead use non-static methods and add a class binding in `App\Providers\AppServiceProvider`.

### Emails

All emails must pass through a css inliner before being sent, so do not use the laravel provided `Mail` class directly but instead use `App\Library\Mail`. To create an new email, extend the `emails.ink-template` view and use a table-based layout as describe by the [Zurb Ink documentation](http://zurb.com/ink/docs.php).

### User Scope

When using the `User` model, note that it has an applied scope which filters out inactive users in the same way the default `SoftDelete` scope does. The scope adds a `withInactive()` builder method equivalent to the Laravel `withTrashed()`. They can be used together to retrieve a user who was made inactive before being deleted.


## Create new service

`php artisan tinker`

`App\Service::create(['name' => 'CataLex Sign', 'is_paid_service' => true]);`

# Todo

Upgrade to Laravel 5.5 LTS.

Move out all authorisation logic (eg. checking if a user is a global admin) from controllers to middleware applied in the routes file.

Dead code elimination.

Consistent styling of pages.

Better, more intuitive navigation between pages. Construct Information Architecture first, so it is well thought out.

Fix footer - it looks weird.

Better documentation.

More consistent use of Laravel features, eg. Route Model Binding, using $request->user() instead of facade, etc.

Create code style and bring all code into new code style.

Clean up billing simulation tests.

Move to new Laravel OAuth.

Use the OAuth classes for service based api auth, instead of looking in the db for the secret to match.