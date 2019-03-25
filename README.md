# Laravel Scheduler

Dynamically schedule your [Laravel tasks](https://laravel.com/docs/scheduling) using artisan commands.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/koomai/laravel-scheduler.svg?style=flat-square)](https://packagist.org/packages/koomai/laravel-scheduler)
[![Build Status](https://travis-ci.org/koomai/laravel-scheduler.svg?branch=master)](https://travis-ci.org/koomai/laravel-scheduler)
[![StyleCI](https://github.styleci.io/repos/177488391/shield?branch=master)](https://github.styleci.io/repos/177488391)

Laravel Scheduler allows you to add, view and remove scheduled tasks in a database via artisan commands. This is 
particularly useful when you want to schedule tasks without having to redeploy code. 

## Installation

You can install the package via composer:

```bash
composer require koomai/laravel-scheduler
```

## Usage

### Add Scheduled Task

`php artisan schedule:add`

### List scheduled tasks (in database)

`php artisan schedule:list`

### Show/Delete a scheduled task (in database)

`php artisan schedule:show <id>`

`php artisan schedule:delete <id>`

### Show due scheduled tasks (from both database and `Console\Kernel`)

`php artisan schedule:due`

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.asdfas

## Credits

- [Sid K](https://github.com/koomai)
- [Spatie](https://github.com/spatie/skeleton-php)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
