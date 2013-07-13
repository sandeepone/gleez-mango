## Mango Reader

Module for [Gleez CMS](http://gleezcms.org/) that monitors all system events and recording them in a log using [MongoDB DBMS](http://www.mongodb.org)!

## Overview

**MongoDB** (from "hu|mongo|us") is an open source document-oriented database system developed and supported by [10gen](http://www.10gen.com/). It is part of the NoSQL family of database systems. Instead of storing data in tables as is done in a "classical" relational database, MongoDB stores structured data as JSON-like documents with dynamic schemas (MongoDB calls the format BSON), making the integration of data in certain types of applications easier and faster.

## Description

**Mango Reader** is a [Gleez CMS](http://gleezcms.org/) module and it's a simple object wrapper for the [Mongo PHP](http://php.net/manual/en/book.mongo.php) driver. It monitors your website, capturing system events in a log to be reviewed by an authorized individual at a later time. The log is simply a list of recorded events containing usage data, performance data, errors, warnings and operational information.

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


##  Special thanks to

- [sign](https://github.com/sergey-sign) - Code
- [sandeepone](https://github.com/sandeepone) - Gleez Team

This module has appeared thanks to such wonderful projects as:

- [Wouterrr/MangoDB](https://github.com/Wouterrr/MangoDB) - MongoDB for KO 3.x
- [colinmollenhour/mongodb-php-odm](https://github.com/colinmollenhour/mongodb-php-odm) - A simple but powerful set of wrappers for using MongoDb in PHP (also a Kohana 3 module)


***

[Installation](https://github.com/sergeyklay/gleez-mango/wiki/Installation) | [Changelog](https://github.com/sergeyklay/gleez-mango/wiki/Changelog) | [Contributors](https://github.com/sergeyklay/gleez-mango/wiki/Contributors) | [Contributing](https://github.com/sergeyklay/gleez-mango/wiki/Contributing) | [License](https://github.com/gleez/cms/wiki/License)