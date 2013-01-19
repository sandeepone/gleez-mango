<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Класс доступа к объектам MongoDB
 *
 * Перед использованием нужно создать экземпляр базы данных.
 * По умолчанию класс будет использовать  файл конфигурации из
 * MODPATH/config/mongo.php или APPPATH/config/mongo.php и конфигурационную
 * группу 'default'.
 *
 *    // Пример
 *    $db = Mango::instance();
 *
 * Так же есть альтернативный способ создания экземпляра MongoDB.
 * Например, тут создастся экземпляр под именем mongo и будет
 * использованна база данных test:
 *
 *    // Пример
 *    $db = Mango::instance('mongo', array('database' => 'test'));
 *
 * ### Системные требования
 *
 *  PHP 5.3 или старше
 *  PHP-расширение Mongodb 1.3 или старше
 *
 * @package   Mango
 * @category  Database
 * @author    Яковлев Сергей (me@klay.me)
 * @version   0.1.1.1
 * @copyright (c) 2013 Яковлев Сергей
 * @license   GPLv3
 * @link      http://php.net/manual/ru/book.mongo.php MongoDB Native Driver
 *
 * @todo Разделить класс на 3:
 * - Mango_Database для управления базой данных и соединениями
 * - Mango_Collection для управления коллекциями
 * - Mango_Document для управления коллекциями
 *
 * @todo Реализовать профилирование
 */

class Mango_Database {

  /** @var array Экземпляры Mango_Database */
  public static $instances = array();

  /** @var string Название конфигурационной группы */
  public static $default = 'default';

  /** @var string Название экземпляра базы данных */
  protected $_name;

  /** @var array Хранит конфигурацию */
  protected $_config;

  /** @var boolean Статус соединения с базой данных */
  protected $_connected = FALSE;

  /** @var object Не обработанное соединение с сервером */
  protected $_connection;

  /** @var MongoDB Не обработанное (сырое) соединение с базой данных */
  protected $_db;

  /** @var string Имя базы данных по умолчанию */
  const MANGO_DB_NAME = 'Cerber';

  /** @var string Версия модуля */
  const MANGO_VERSION = '0.1.1.1';

  /**
   * Получение экземпляра Mango_Database
   *
   * @param   string    $name   Название конфигурационной группы [Опционально]
   * @param   array     $config Конфигурация MongoDB [Опционально]
   * @return  Mango_Database    Экземпляр базы данных
   */
  public static function instance($name = NULL, array $config = NULL)
  {
    if (is_null($name))
    {
      $name = self::$default;
    }
    if (! isset(self::$instances[$name]))
    {
      if (is_null($config))
      {
        // Загрузка конфигурации для этой базы данных
        $config = Kohana::$config->load('mango')->$name;
      }

      new self($name,$config);
    }

    // Возвращаем экземпляр базы данных
    return self::$instances[$name];
  }

  /**
   * Конструктор класса
   *
   * @param   string    $name   Имя экземпляра базы данных
   * @param   array     $config Конфигурация MongoDB
   * @throws  Exception         При отсутствии php-расширения mongo
   * @throws  Kohana_Exception  При отсутствии обязательных параметров конфигурации
   */
  protected function __construct($name, array $config)
  {
    if (! extension_loaded('mongo'))
    {
      throw new Exception('PHP-расширение mongo не установлено или выключено.');
    }

    // Формируем имя экземпляра базы данных
    $this->_name = $name;

    // Формируем конфигурацию
    $this->_config = $config;

    // Подготавливаем имя базы данных
    $this->_db = isset($this->_config['connection.database'])
      ? $this->_config['connection.database']
      : self::MANGO_DB_NAME;

    // Подготавливаем хост
    $host = isset($this->_config['connection.hostname'])
      ? $this->_config['connection.hostname']
      : NULL;

    // Подготавливаем имя пользователя базы данных
    $user = isset($this->_config['connection.username'])
      ? $this->_config['connection.username']
      : NULL;

    // Подготавливаем пароль пользователя базы данных
    $passwd = isset($this->_config['connection.password'])
      ? $this->_config['connection.password']
      : NULL;

    // Подготавливаем дополнительные опции
    $opt = Arr::get($this->_config['connection'], 'options', array());

    // Формируем строку для соединения
    $prepared = $this->_prepare_connection($host, $user, $passwd);

    // Подготавливаем соединение
    $this->_connection = new MongoClient($prepared, $opt);

    // Очистка памяти
    unset($host, $user, $passwd, $opt, $prepared);

    // Сохраняем экземпляр базы данных
    self::$instances[$name] = $this;
  }

  /** Деструктор */
  final public function __destruct()
  {
    try
    {
      $this->disconect();
      $this->_connection = NULL;
      $this->_connected = FALSE;
    }
    catch(Exception $e)
    {
      // В деструкторе не работает исключение
    }
  }

  /**
   * Возвращает название экземпляра базы данных
   *
   * @return  string
   */
  final public function __toString()
  {
    return $this->_name;
  }

  /**
   * Выполнение команды
   *
   * @param   string  $cmd    Команда для выполнения
   * @param   array   $args   Аргументы [Опционально]
   * @param   array   $values Значения, передаваемые в команду [Опционально]
   * @return  mixed   Результат выполнения метода переданного в строке `$cmd`
   */
  public function _call($cmd, array $args = array(), $values = NULL)
  {
    // Если соединения нет - выполняем connect()
    $this->_connected OR $this->connect();

    if (isset($args['collection']))
    {
      $c = $this->_db->selectCollection($args['collection']);
    }

    switch ($cmd)
    {
      case 'batch_insert':
        $responce = $c->batchInsert($values, array('continueOnError' => TRUE));
      break;
      case 'count':
        $responce = $c->count($args['query']);
      break;
      case 'find':
        $responce = $c->find($args['query'], $args['fields']);
      break;
      case 'find_one':
        $responce = $c->findOne($args['query'], $args['fields']);
      break;
      case 'remove':
        $responce = $c->remove($args['criteria'], $args['options']);
      break;
      case 'drop':
        $responce = $c->drop();
      break;
    }

    return $responce;
  }

  /**
   * Формирует адрес для конекта
   *
   * @param   string  $host   Хост базы данных
   * @param   string  $user   Имя пользователя бд
   * @param   string  $passwd Пароль пользователя базы данных
   * @return  string  Подготовленная строка для соединения
   */
  protected function _prepare_connection($host, $user, $passwd)
  {
    if (is_null($host))
    {
      $host = ini_get('mongo.default_host').':'.ini_get('mongo.default_port');
    }

    if (! is_null($user) AND ! is_null($passwd))
    {
      return 'mongodb://' . $user . ':' . $passwd . '@' . $host . '/' . $this->_db;
    }

    return 'mongodb://' . $host . '/' . $this->_db;
  }

  /**
   * Получение экземпляра MongoDB напрямую
   *
   * @return MongoDB
   */
  public function db()
  {
    $this->_connected OR $this->connect();

    return $this->_db;
  }

  /**
   * Соединение с базой данных
   *
   * @return  TRUE при успешном соединении
   * @throws  Kohana_Exception  При ошибке соединения
   */
  public function connect()
  {
    // Если соединения нет
    if(! $this->_connected)
    {
      try
      {
        // Подключаемся к серверу
        $this->_connected = $this->_connection->connect();
      }
      catch (MongoConnectionException $e)
      {
        // Невозможно соединиться с сервером баз данных
        throw new Kohana_Exception('Невозможно соединиться с сервером MongoDB. Сервер MongoDB ответил: :message',
          array
          (
            ':message' => $e->getMessage()
          )
        );
      }

      $this->_db = $this->_connection->selectDB("$this->_db");
    }

    return $this->_connected;
  }

  /** Разъединение с базой данных */
  protected function disconect()
  {
    if ($this->_connected)
    {
      $this->_connected = $this->_connection->close();
      $this->_db = "$this->_db";
    }

    return $this->_connected;
  }

  /**
   * Подсчёт документов в коллекции
   *
   * @param   string  $collection Название коллекции
   * @param   array   $query      NoSQL запрос
   * @return  integer Результат подсчёта строк
   *
   * @link    http://php.net/manual/en/mongocollection.count.php MongoCollection::count()
   */
  public function count($collection, array $query = array())
  {
    return $this->_call('count', array(
      'collection' => $collection,
      'query'      => $query
    ));
  }

  /**
   * Получает документы из коллекции
   *
   * @param   string      $collection Название коллекции
   * @param   array       $query      NoSQL запрос [Опционально]
   * @param   array       $fields     Поля, по которым ищем в запросе [Опционально]
   * @return  MongoCursor
   *
   * @link    http://php.net/manual/en/mongocollection.find.php MongoCollection::find()
   */
  public function find($collection, array $query = array(), array $fields = array())
  {
    return $this->_call('find', array(
      'collection'  => $collection,
      'query'       => $query,
      'fields'      => $fields
    ));
  }

  /**
   * Получает 1 документ из коллекции
   *
   * @param   string      $collection Название коллекции
   * @param   array       $query      NoSQL запрос [Опционально]
   * @param   array       $fields     Поля, по которым ищем в запросе [Опционально]
   * @return  MongoCursor
   *
   * @link    http://php.net/manual/en/mongocollection.findone.php MongoCollection::findOne()
   */
  public function find_one($collection, array $query = array(), array $fields = array())
  {
    return $this->_call('find_one', array(
      'collection'  => $collection,
      'query'       => $query,
      'fields'      => $fields,
    ));
  }

  /**
   * Удаление документа из коллекции
   *
   * @param   string  $collection Имя коллекции
   * @param   array   $criteria   Критерии поиска
   * @param   array   $options    Дополнительные опции [Опционально]
   * @return  boolean|array
   *
   * @link    http://php.net/manual/en/mongocollection.remove.php MongoCollection::remove()
   */
  public function remove($collection, array $criteria, $options = array())
  {
    return $this->_call('remove', array(
      'collection'  => $collection,
      'criteria'    => $criteria,
      'options'     => $options
    ));
  }

  /**
   * Удаление коллекции
   *
   * @param   string  $collection Имя коллекции
   * @return  array   Ответ базы данных в виде массива
   *
   * @link    http://php.net/manual/en/mongocollection.drop.php MongoCollection::drop()
   */
  public function drop($collection)
  {
    return $this->_call('drop', array(
      'collection'  => $collection
    ));
  }

  /**
   * Массовая вставка нескольких документов в коллекцию
   *
   * Замечание: Если в массиве `$a` переданы объекты,
   * они не должны иметь свойства `protected` или `private`
   *
   * @param   string      $collection Имя коллекции
   * @param   array       $a          Массив массивов или объектов
   * @return  mixed
   *
   * @link    http://php.net/manual/en/mongocollection.batchinsert.php MongoCollection::batchInsert()
   */
  public function batch_insert($collection, array $a)
  {
    return $this->_call('batch_insert', array('collection' => $collection), $a);
  }

}