# Mango Reader

Module that monitors all system events and recording them in a log using [MongoDB DBMS](http://www.mongodb.org)!
And also provides the driver for caching with MongoDB support!

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

### Current stable versions

- **Current master** for Gleez CMS 0.9.8.2 or higher [Download](https://github.com/sergeyklay/gleez-mango/archive/master.zip)
- **0.1.1.3** for Gleez CMS 0.9.8.2 or higher [Download](https://github.com/sergeyklay/gleez-mango/archive/0.1.1.3.zip)
- **Nightly builds** for Gleez CMS 0.9.8.2 or higher [Download](https://github.com/sergeyklay/gleez-mango/archive/next.zip)

### Old stable versions

- **0.1.1.1** for Gleez CMS 0.9.8.2 or higher [Download](https://github.com/sergeyklay/gleez-mango/archive/0.1.1.1_gleez.zip)


## System Requirements

- [PHP](http://php.net/) 5.3 or higher
- [Gleez CMS](http://gleezcms.org/) 0.9.8.2 or higher
- [MondoDB](http://mongodb.org/) 2.3.3 or higher
- [PHP-extension](http://php.net/manual/en/mongo.installation.php) MongoDB 1.3 or higher
- Gleez Cache (optional for caching with MongoDB support)
- ACL (optional for module specific permissions)


## Features

- View list of all events
- View single log event
- Delete event from log
- Drop system log collection
- Store cache into MongoDB Collection


## Future Plans

- Divide the `Mango_Database Class` into the following three:
 - `Mango_Database Class`: for database and connection managing
 - `Mango_Collection Class`: for collection managing
 - `Mango_Document Class`: for document managing
- Implement Profiling
- Implement Session Storage ( *in the long term* )
- More pure and correct English in the documentation and the string resources


## Installation & Usage

- [Download](https://github.com/sergeyklay/gleez-mango/archive/master.zip) module from its GitHub [homepage](https://github.com/sergeyklay/gleez-mango)

- Include Mango Reader into your module path. For example:
```php
  /**
   * Enable modules. Modules are referenced by a relative or absolute path.
   */
  Kohana::modules(array(
    'gleez'     => MODPATH.'gleez',      // Gleez Core Module
    'user'      => MODPATH.'user',       // User and group Administration
    'cache'     => MODPATH.'cache',      // Caching with multiple backends
    'database'  => MODPATH.'database',   // Database access
    'image'     => MODPATH.'image',      // Image manipulation
    'captcha'   => MODPATH.'captcha',    // Captcha implementation
    'unittest'  => MODPATH.'unittest',   // Unit testing
    'codebench' => MODPATH.'codebench',  // Benchmarking tool
    'userguide' => MODPATH.'userguide',  // User guide and API documentation
    'mango'     => MODPATH.'mango',      // Mango Reader module
  ));
```

- Attach the MangoDB write to logging:
```php
  // Disable logging into files
  // Kohana::$log->attach(new Gleez_Log_File(APPPATH.'logs'));

  // Enable logging into MongoDB database
  Kohana::$log->attach(new Log_Mango());
```

- Go to `admin/logs` to view the system log

- Go to `admin/logs/view/<_id>` to view a specific event, where <_id> is event id. For example: `admin/logs/view/511f6e307897313c5c000048`

- For all routes see `MODPATH/<mango_dir>/init.php`

- Use `MODPATH/<mango_dir>/config/mango.php` as an example for creating `APPATH/config/mango.php` with your individual settings


## Contributors

- [Sergey Yakovlev](https://github.com/sergeyklay) - Code

Now that you're here, why not start contributing as well? :)

### Contribute to Mango Reader

If you wish to contribute to Mango Reader, please be sure to read/subscribe to the following resources:
- [Kohana Conventions and Coding Style](http://kohanaframework.org/3.2/guide/kohana/conventions)
- [Gleez CMS Wiki](https://github.com/gleez/cms/wiki)
- [Mango Reader Wiki](https://github.com/sergeyklay/gleez-mango/wiki)

If you are working on new features, or refactoring an existing component, please create a proposal.


##  Special thanks to

- [sign](https://github.com/sergey-sign) - Code
- [sandeepone](https://github.com/sandeepone) - Gleez Team


## Changelog

**0.1.1.3** - *February 15 2013*
- Created logo
- Maked [home page](http://sergeyklay.github.com/gleez-mango/) for module
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
