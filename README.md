# Mango Reader

Module that monitors all system events and recording them in a log using [MongoDB DBMS](http://www.mongodb.org)!

This module has appeared thanks to such wonderful projects as:

+ [Wouterrr/MangoDB](https://github.com/Wouterrr/MangoDB) - MongoDB for KO 3.x
+ [colinmollenhour/mongodb-php-odm](https://github.com/colinmollenhour/mongodb-php-odm) - A simple but powerful set of wrappers for using MongoDb in PHP (also a Kohana 3 module)

## Overview

**MongoDB** (from "hu|mongo|us") is an open source document-oriented database system developed and supported by
[10gen](http://www.10gen.com/). It is part of the NoSQL family of database systems. Instead of storing data in
tables as is done in a "classical" relational database, MongoDB stores structured data as JSON-like documents with
dynamic schemas (MongoDB calls the format BSON), making the integration of data in certain types of applications
easier and faster.


## Description

**Mango Reader** is a [Gleez CMS](http://gleezcms.org/) module and it's a simple object wrapper for the
[Mongo PHP](http://php.net/manual/en/book.mongo.php) driver. It monitors your website, capturing system
events in a log to be reviewed by an authorized individual at a later time. The log is simply a list of
recorded events containing usage data, performance data, errors, warnings and operational information.

It is vital to check the log report on a regular basis as it is often the only way to tell what is going on.


## Download

- **Current master** for Gleez CMS 0.9.26 or higher: [Download](https://github.com/sergeyklay/gleez-mango/archive/master.zip)
- **0.1.3** for Gleez CMS 0.9.26 or higher: [Download](https://github.com/sergeyklay/gleez-mango/archive/v0.1.3.zip)
- **0.1.2** for Gleez CMS 0.9.26 or higher: [Download](https://github.com/sergeyklay/gleez-mango/archive/v0.1.2.zip)

## System Requirements

- [PHP](http://php.net/) 5.3 or higher
- [Gleez CMS](http://gleezcms.org/) 0.9.26 or higher
- [MondoDB](http://mongodb.org/) 2.4 or higher
- [PHP-extension](http://php.net/manual/en/mongo.installation.php) MongoDB 1.4.0 or higher


## Features

- View list of all logs
- View single log
- Delete any entry from system log
- Drop system log collection
- Store cache into MongoDB Collection. WIP


## Future Plans

- Implement Cache Storage
- Implement Session Storage
- Implement GUI for settings
- More pure and correct English in the documentation and the string resources


## Installation & Usage

- [Download](https://github.com/sergeyklay/gleez-mango/archive/master.zip) module from its GitHub homepage

- Include Mango Reader into your module path. For example:
```php
  /**
   * Enable modules. Modules are referenced by a relative or absolute path.
   */
  Kohana::modules(array(
     'user'      => MODPATH.'user',       // User and group Administration
     'database'  => MODPATH.'database',   // Database access
     'image'     => MODPATH.'image',      // Image manipulation
  // 'captcha'   => MODPATH.'captcha',    // Captcha implementation
  // 'unittest'  => MODPATH.'unittest',   // Unit testing
  // 'codebench' => MODPATH.'codebench',  // Benchmarking tool
  // 'userguide' => MODPATH.'userguide',  // User guide and API documentation
     'mango'     => MODPATH.'mango',      // Mango Reader module
  ));
```

- Attach the MangoDB write to logging:
```php
  // Disable logging into files
  // Kohana::$log->attach(new Log_File(APPPATH.'logs'));

  // Enable logging into MongoDB database
  Kohana::$log->attach(new Log_Mango());
```

- Go to `admin/logs` to view the system log
- Go to `admin/logs/view/<_id>` to view a specific event, where <_id> is event id. For example: `admin/logs/view/511f6e307897313c5c000048`
- For all routes see `MODPATH/<mango_dir>/init.php`
- Use `MODPATH/<mango_dir>/config/mango-reader.php` as an example for creating `APPATH/config/mango-reader.php` with your individual settings


## Contribute to Mango Reader

### Contributors

- [Sergey Yakovlev](https://github.com/sergeyklay) - Code, Russian i18n

Now that you're here, why not start contributing as well? :)

### Contributing info

If you wish to contribute to Mango Reader, please be sure to read/subscribe to the following resources:
- [Kohana Conventions and Coding Style](http://kohanaframework.org/3.2/guide/kohana/conventions)
- [Gleez Contributing TODO](https://github.com/gleez/cms/wiki/Contributing)
- [Mango Reader issues](https://github.com/sergeyklay/gleez-mango/issues)

If you are working on new features, or refactoring an existing component, please create a proposal.


##  Special thanks to

- [sign](https://github.com/sergey-sign) - Code
- [sandeepone](https://github.com/sandeepone) - Gleez Team
- [Wouterrr](https://github.com/Wouterrr) - MangoDB
- [colinmollenhour](https://github.com/colinmollenhour) - mongodb-php-odm

## Changelog

**0.1.3** - *Jule 11 2013*
- Used new Mango_Collection API
- Fixed README.md
- i18n fixes
- Tagging version `0.1.3`
- Minor changes (see [commits](https://github.com/sergeyklay/gleez-mango/commits/master) diff)

**0.1.2** - *Jule 08 2013*
- Log writer, `Mango` and `Mango_Collection` classes moved to the Gleez
- Redesigned views
- Spared from additional css file
- Simplified and renamed configuration (`config/mango.php -> config/mango-reader.php`)
- Now supported versions MongoDB 2.4 or higher and php-mongo 1.4 or higher
- i18n support: Russian
- Tagging version `0.1.2`

**0.1.1.3** - *February 15 2013*
- Created logo
- Makes [home page](http://sergeyklay.github.com/gleez-mango/) for module
- Added cache driver
- Added Mango config() setter and getter
- Fixed constructor (added param `$config` ability)
- Amended API PHPDoc
- Tagging version `0.1.1.3`
- Minor changes (see [commits](https://github.com/sergeyklay/gleez-mango/commits/master) diff)

**0.1.1.2** - *January 22 2013*
- Modified Mango singleton
- Modified module file system
- Minor changes (see [commits](https://github.com/sergeyklay/gleez-mango/commits/master) diff)

**0.1.1.1** - *January 19 2013*
- Added I18n ability
- Added ability to clear all messages from the log
- Tagging version `0.1.1.1_gleez`
- Minor changes (see [commits](https://github.com/sergeyklay/gleez-mango/commits/master) diff)

**0.1.1.0** - *January 17 2013*
- Initial release
