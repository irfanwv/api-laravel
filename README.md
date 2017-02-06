# Laravel API

The API.

## Official Documentation

This document will probably be the jist of it.  Read below.

## Contributing

This is a private project.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).

This has traditionally been a 'fun' paragraph to write.
First, you'll have to read about what the MIT license actually entails (link above).
Then, we have to distinguish between our proprietary parts and our open source parts.
If MIT is anything like GNU, the open source parts must continue to be open source (or maybe put differently, you can't lay claim to them on behalf of the business).
I usually say something simple like, most stand alone components are open source and we reserve the right to use, re-use, update them, change their apis, do whatever we want.  Any 'business rules or logic' will remain proprietary.

## Requirements

- Apache >= 2.4
  * mod_rewrite
  * I'm not saying don't use ngingx, just that you're on your own if you do.

- PostgreSQL >= 9.1
  * postgresql-contrib, JSON and unaccent extensions

- PHP >= 5.5
  * some modules

- [Composer](https://getcomposer.org/)
  * there are no global dependencies at this time

- [NodeJS](https://nodejs.org/en/)
  * [Bower](http://bower.io/).  `# npm install -g bower`
  * [Gulp](http://gulpjs.com/). `# npm install -g gulp`

## Installation

### API
#### 1.	Create the database.
`# sudo -u postgres psql -c "CREATE DATABASE passporttoprana;`
`# sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE passporttoprana TO webuser;`

#### 2.	Configure the web server
`# sudo nano /etc/apache2/sites-available/api.passporttoprana.com.conf`
`API_URL` can be anything you like, i personally use `api.p2p.impul.se`
`YOUR_EMAIL` is self-explanatory.
If you changed the name of your SSL files, make sure you change that in the config.
This will forward any non-secure requests to the HTTPS site.

    <VirtualHost *:80>
	    ServerAdmin YOUR_EMAIL
	
	    ServerName API_URL
	
	    Redirect permanent / https://API_URL
	</VirtualHost>

	<VirtualHost _default_:443>
	    ServerAdmin YOUR_EMAIL

	    DocumentRoot "/var/www/passporttoprana/api/public"

	    ServerName API_URL

	    ErrorLog "/var/www/passporttoprana/logs/api-error.log"
	    CustomLog "/var/www/passporttoprana/logs/api-access.log" common

	    <Directory "/var/www/passporttoprana/api/public">

	        Options FollowSymLinks Includes ExecCGI

	        AllowOverride all

	        Require all granted

	    </Directory>

	    SSLEngine on
	    SSLOptions +StrictRequire

	    <Directory />
	        SSLRequireSSL
	    </Directory>

	    SSLProtocol -all +TLSv1 +SSLv3
	    SSLCipherSuite HIGH:MEDIUM:!aNULL:+SHA1:+MD5:+HIGH:+MEDIUM

	    SSLCertificateFile /var/www/passporttoprana/ssl/server.crt
	    SSLCertificateKeyFile /var/www/passporttoprana/ssl/server.key
    
	    SSLVerifyClient none
	    SSLProxyEngine off

	    <IfModule mime.c>
	        AddType application/x-x509-ca-cert      .crt
	        AddType application/x-pkcs7-crl         .crl
	    </IfModule>

	    Header set Access-Control-Allow-Origin "*"
	    Header set Access-Control-Allow-Headers "Authorization,Content-Type,Accept,Origin,User-Agent,DNT,Cache-Control,X-Mx-ReqToken,Keep-Alive,X-Requested-With,If-Modified-Since"
	    Header set Access-Control-Allow-Methods "GET, POST, PUT, HEAD, OPTIONS, DELETE"
	    Header set Access-Control-Expose-Headers "Authorization"

	</VirtualHost>

#### 3.	 Clone the development branch.
`# cd /var/www/passporttoprana/`
`# git clone git@git.impulsesolutions.ca:passporttoprana/api.git -b develop api`

#### 4.	 Install the project dependencies.
`# cd api`
`# composer install`

#### 5.	 Configure your environment.
`# cp .env.example .env`
Fill out some values in the .env file.

#### 6.	 Run the migrator.
`# php artisan migrate`

You may run into an error where the console subsystem depends on business logic that requires database records.  If that's the case, go to App\Console\Kernel.php and comment out the custom console commands.  Make sure to uncomment them out again when this runs.

#### 7.	 Create yourself an admin account.
Open database\seeds\UserTableSeeder.php and copy one of the ones already there.

#### 8.	 Run the seeder.
`# php artisan db:seed`

#### 9.	 If everything works, and you can get a response from API_URL, then you can:

#### 10.	 Create your own branch.
`# git branch YOUR_NAME; git checkout YOUR_NAME;`

#### 11.	 Push your new branch to the server
`# git add -A`
`# git commit -am"environment configuration"`
`# git push origin YOUR_NAME`