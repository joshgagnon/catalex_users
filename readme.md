# CataLex User Management Portal

[![Build Status](https://travis-ci.org/joshgagnon/catalex_users.svg?branch=master)](https://travis-ci.org/joshgagnon/catalex_users)

This app provides users, organisations and global admins an interface to manage their own (and others) user and billing details. It also provides login support for the Law Browser.

## Deployment

### Local Dev Copy

Any PHP enabled webserver, i.e. Apache + mod_php, but nginx + php-fpm  is recommended both to more closely match the live environment and because it's easier to configure. Likewise MySQL and sqlite will both work, but postgresql is recommended. The following packages will provide such a setup on Ubuntu:

`apt-get install git postgresql nginx curl php5-fpm php5-cli php5-mcrypt php5-curl php5-gd php5-pgsql`

Composer must be installed globally, for example:

`curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/bin`

Enable php5-mcrypt with `sudo php5enmod mcrypt`

You will need an up to date version of node and npm installed globally - on Ubuntu and some other distros the package managed version will be too far behind, so grab them from the [node website](https://nodejs.org/).

Install gulp globally with `npm install -g gulp`

Create a new empty database - i.e. with postgres: `createdb catalex_users -O yourusername`

Clone the repo, then cd into the folder an run

    sudo chown -R www-data:www-data storage  # Your webserver user
    composer install
    gulp
    cp .env.example .env

Edit .env to match your environment, in particular set the database name, username and password, then run: `./artisan migrate --seed`

Finally, add an entry to your hosts file or dnsmasq config to point a domain to localhost then add an nginx config file to respond to that domain with the `public` folder, for example

    server {
        listen 80;

        root /home/code/catalex/catalex_users/public;
        index index.php;

        server_name catalex-users.dev;

        location / {
            try_files $uri $uri/ /index.php$is_args$args;
        }

        # pass the PHP scripts to FastCGI server listening on 127.0.0.1:9000
        location ~ \.php$ {
            fastcgi_split_path_info ^(.+\.php)(/.+)$;
            # NOTE: You should have "cgi.fix_pathinfo = 0;" in php.ini

            # With php5-cgi alone:
            # fastcgi_pass 127.0.0.1:9000;
            # With php5-fpm:
            fastcgi_pass unix:/var/run/php5-fpm.sock;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
        }
    }

Restart the nginx and php services to load the new configurations

    sudo service nginx restart
    sudo service php5-fpm restart

### Deploying Live

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

Most code in written in idiomatic Laravel style to avoid any surprises. See the [Laravel 5 documentation](http://laravel.com/docs/5.0) for further details. Exceptions to this rule and some other notes are below.

### Generic Functionality

To add non-model specific, non-controller functionality to the app, the best place is `app/Library`. Functionality that won't need to be mocked for testing should be made as a static function directly accessible on in a library class. Testable functions should not be static, even if they require no state - instead use non-static methods and add a class binding in `App\Providers\AppServiceProvider`.

### Emails

All emails must pass through a css inliner before being sent, so do not use the laravel provided `Mail` class directly but instead use `App\Library\Mail`. To create an new email, extend the `emails.ink-template` view and use a table-based layout as describe by the [Zurb Ink documentation](http://zurb.com/ink/docs.php).

### User Scope

When using the `User` model, note that it has an applied scope which filters out inactive users in the same way the default `SoftDelete` scope does. The scope adds a `withInactive()` builder method equivalent to the Laravel `withTrashed()`. They can be used together to retrieve a user who was made inactive before being deleted.


### Tests
just run:
./vendor/bin/phpunit
