## STAPI

The Star Trek API. Inspired by [SWAPI](https://swapi.co/).

[![Build Status](https://travis-ci.org/aknackd/stapi.svg?branch=master)](https://travis-ci.org/aknackd/stapi)
[![license](https://img.shields.io/github/license/aknackd/stapi.svg?maxAge=2592000)]()

### Requirements

* PHP >= 5.5.9
* [Composer](https://getcomposer.org)
* [7-zip](http://www.7-zip.org)

### Quick Setup

```
$ composer install
$ php artisan migrate
$ php artisan db:seed
$ php artisan serve
$ curl http://localhost:8000/api/v1/series
```

### Importing data from Memory Alpha

The seed data included in this project only contains the Star Trek series and films up to 2016. Episodes from all series can be imported from [Memory Alpha](http://memory-alpha.wikia.com/wiki/Portal:Main), a community wiki containing in-depth information on Star Trek (canon sources only).

Before you begin importing data from Memory Alpha, you must first install [7-zip](http://www.7-zip.org) and ensure that the `7za` binary is in your `$PATH`. Once that has been fulfilled, simply run the import artisan task like so:

```
$ php artisan stapi:import
```

This will download the [latest XML database dump from Memory Alpha](http://memory-alpha.wikia.com/wiki/Memory_Alpha:Database_download) and import its contents. Note that this will locally cache the _uncompressed_ database dump in your OS-specific cache directory so you don't have to download it every time the import is ran. Execute `php artisan stapi:import --help` for all available options.

There are no plans to incorporate data from [Memory Beta](http://memory-beta.wikia.com/wiki/Main_Page) (includes novels, comic books, videogames, and any other licensed works).


#### Licensing Notice

Memory Alpha content is licensed under the [CC-BY-NC license](http://memory-alpha.wikia.com/wiki/Memory_Alpha:Creative_Commons_License) and the terms and conditions of the [Wikia Licensing policy](http://www.wikia.com/Licensing).

### TODO

- [X] Import `starship`
- [ ] Import `sharship class`
- [ ] Import `individual`
- [ ] Import `planet`