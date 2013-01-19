# Mango Reader

_Модуль, который следит за всеми системными событиями и записывает их в журнал, используя [СУБД MongoDB] (http://www.mongodb.org)!_


## Введение

**MongoDB** (от "hu **mongo** us") это документно-ориентированная система управления базами данных (СУБД) с открытым исходным кодом
разрабатываемая и поддерживаемая [10gen] (http://www.10gen.com/). Она является частью NoSQL-семейства систем баз данных.
Вместо того, чтоб хранить данные в таблицах, как в "классических" реляционных базах данных, MongoDB хранит структурированные данные
в виде JSON-подобных документов в динамических схемах (MongoDB использует формат BSON), позволяющая осуществлять интеграцию данных
в определенных типах приложений проще и быстрее.



## Описание

**Mango Reader** это модуль для [Cerber CMS] (http://cerbercms.klay.me/) и [Gleez CMS] (http://gleezcms.org/) и он является простой
оболочкой над объектом драйвера [Mongo PHP] (http://php.net/manual/en/book.mongo.php). Модуль отслеживает системные события на вашем
сайте и заносит их в журнал. Привилегированным пользователям предоставляется возможность просмотра журнала. Журнал это простой список из
зарегистрированных событий содержащих в себе информацию об использовании ресурсов сайта, данные о производительности, ошибки,
предупреждения и другую оперативную информацию.

Очень важно проверять отчёты системных событий на регулярной основе, т.к. это единственный способ узнать, что же на самом деле
происходит на Вашем сайте.


## Текущие доступные версии

- **0.1.1.1** для Cerber CMS 0.1.1.0 или выше [Скачать] (https://github.com/sergeyklay/gleez-mango/archive/cerber.zip)
- **0.1.1.1** для Gleez CMS 0.9.8.1 или выше [Скачать] (https://github.com/sergeyklay/gleez-mango/archive/master.zip)


## Системные требования

- [PHP] (http://php.net/) 5.3 или выше
- [PHP-расширение] (http://php.net/manual/en/mongo.installation.php) MongoDB 1.3 или выше
- [Gleez CMS] (http://gleezcms.org/) 0.9.8.1 или выше
- [Cerber CMS] (http://cerbercms.klay.me/) 0.1.1.0 или выше
- ACL (опционально, для специфичных модулю привилегий)


## Возможности

- Просмотр списка событий
- Просмотр конкретного события
- Удаление события из журнала
- Очистка всего журнала


## Планы на будущее

- Разделить класс `Mango_Database` на три следующие класса:
 - Класс `Mango_Database`: Управление базой данных и соединениями
 - Класс `Mango_Collection`: Управление коллекциями
 - Класс `Mango_Document`: Управление документами
- Реализовать профилирование
- Реализовать хранение сессий *(в далёкой перспективе)*
- Более чистый и корректный английский в документации и строках


## Установка и использование

- [Скачать] (https://github.com/sergeyklay/gleez-mango/archive/cerber.zip) модуль с официальной [страницы] (https://github.com/sergeyklay/gleez-mango) на GitHub

- Включить Mango Reader в ваш путь для поиска модулей. Например:
```php
  /**
   * Enable modules. Modules are referenced by a relative or absolute path.
   */
  Kohana::modules(array(
    'cerber'    => MODPATH.'cerber',     // Cerber Core Module
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

- Присоеденить MangoDB к объекту ведения журнала:
```php
  // Disable logging into files
  // Kohana::$log->attach(new Gleez_Log_File(APPPATH.'logs'));

  // Enable logging into MongoDB database
  Kohana::$log->attach(new Log_Mango());
```

- Роутинг смотрите тут `MODPATH/<mango_dir>/init.php`

- Используйте `MODPATH/<mango_dir>/config/mango.php` как пример для создания  `APPATH/config/mango.php` с вашими индивидуальными настройками


## Авторы

- [Яковлев Сергей] (https://github.com/sergeyklay) - Код

Теперь, когда вы здесь, почему бы не начать вносить вклад, а? :)


## Отдельная благодарность

- [sign] (https://github.com/sergey-sign) - Код
- [sandeepone] (https://github.com/sandeepone) - Команда Gleez


## История версий

**0.1.1.1** - *19 января 2013*

- Добавлена возможность локализации модуля (в версии для Gleez CMS)
- Добавлена возможность очистки всего журнала
- Незначительные изменения (смотрите сравнение комитов и файлов)

**0.1.1.0** - *17 января 2013*

- Первый релиз
