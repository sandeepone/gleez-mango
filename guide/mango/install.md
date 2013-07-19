# Requirements

- [PHP](http://php.net/) 5.3.7 or higher
- [Gleez CMS](http://gleezcms.org/) 0.9.26 or higher
- [MondoDB](http://mongodb.org/) 2.4.5 or higher
- [PHP-extension](http://php.net/manual/en/mongo.installation.php) MongoDB 1.4.0 or higher

## Installing using the zip

1. Download the [latest version](https://github.com/sergeyklay/gleez-mango/archive/master.zip) of Mango Reader
2. Upload the contents of the `gleez-mango-master` folder to `MODPATH/mango` path
3. Include Mango Reader into your module path:
~~~
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
 'mango'     => MODPATH.'mango',      // Mango Reader module
));
~~~

4. Attach the MangoDB write to logging
~~~
// Disable logging into files
// Kohana::$log->attach(new Log_File(APPPATH.'logs'));

// Enable logging into MongoDB database
Kohana::$log->attach(new Log_Mango());
~~~

## Git Clone Installation

~~~
git clone https://github.com/sergeyklay/gleez-mango.git mango
~~~

Follow from step 3 of the above instructions

## ArchLinux Installation

~~~
yaourt -S gleez-mango
~~~

Follow from step 3 of the above instructions
