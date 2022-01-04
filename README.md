Laravel Example
===============

Laravel example project used for common programming approaches demonstration.


Requirements
------------

- PHP >= 7.3.0
- Nginx
- MySQL >= 6.0
- Yarn package manager
- Redis 3+
- Supervisor
- Node.js >=14.0, <= 15.0


Installation
------------

#### Installing project

- Clone this repository
- Create and fill `.env` file from `.env.example`
- In project folder make `composer project-install`
- Add `php artisan schedule:run` command to crontab

Crontab example:

```
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

#### Updating project

- Pull changes from remote repository
- In project folder make `composer project-update`

#### Removing project

- Remove cron tasks (see in: storage/app/crontab)


External Services
-----------------

##### Google reCAPTCHA

Project uses [Google reCAPTCHA](https://developers.google.com/recaptcha/) for the form robots protection.
Its integration configured via `RECAPTCHA_SITE_KEY` and `RECAPTCHA_SECRET_KEY`.
These can be obtained from [https://www.google.com/recaptcha/admin](https://www.google.com/recaptcha/admin).


Testing
-------

#### Manual testing

Use following console command to setup data for the manual testing:

```
php artisan db:seed --class DemoSeeder
```


#### Unit and HTTP tests

Run both Unit and HTTP tests:

```
cd /project/root
vendor/bin/phpunit
```

Run Unit tests:

```
vendor/bin/phpunit tests/Unit
```

Run HTTP tests:

```
vendor/bin/phpunit tests/Feature
```
