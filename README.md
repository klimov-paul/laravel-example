Laravel Example
===============

Laravel example project used for common programming approaches demonstration.

Project serves as on-line book library, when registered users can borrow a book for a limited time period.
In order to use the library user should purchase a subscription, which requires recurring payment.

Documentation is at [docs/README.md](docs/README.md).


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

##### Braintree

[Braintree](https://articles.braintreepayments.com/get-started/try-it-out) is used as system payment gateway.

- [Setup Sandbox account](https://www.braintreepayments.com/sandbox)
- [Setup automatic invoice sending](https://articles.braintreepayments.com/control-panel/transactions/email-receipts)
- [Test credit cards](https://developers.braintreepayments.com/reference/general/testing/php#credit-card-numbers)


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
