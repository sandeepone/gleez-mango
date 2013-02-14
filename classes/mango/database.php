<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * ### Introduction
 *
 * This class wraps the functionality of Mongo (connection)
 * and MongoDB (database object) into one class.
 *
 * ### Usage
 *
 * When used with Gleez it can be instantiated simply by:<br>
 * <code>
 *   $db = Mango::instance();
 * </code>
 *
 * The above will assume the `default` configuration from the
 * `APPPATH/config/mongo.php` file (or `MODPATH/config/mongo.php` by default).
 *
 * Alternatively it may be instantiated with the name and
 * configuration specified as arguments:<br>
 * <code>
 *   $db = Mango::instance('mongo', array('database' => 'test'));
 * </code>
 *
 * ### Configuration settings
 *
 * Mango Reader uses configuration groups to create database instances.
 *
 * ### System Requirements
 * - PHP 5.3 or higher
 * - PHP-extension MongoDB 1.3 or higher
 * - MongoDB 2.2.3 or higher
 *
 * @package   Mango\Database
 * @author    Sergey Yakovlev - Gleez
 * @version   0.1.1.3
 * @copyright (c) 2011-2013 Gleez Technologies
 * @license   http://gleezcms.org/license
 *
 * @link      http://php.net/manual/ru/book.mongo.php MongoDB Native Driver
 *
 * @todo      Implement profiling.
 */

class Mango_Database implements Mango_Core {

  /** @var array Mango_Database instances */
  public static $instances = array();

  /** @var string Config group */
  public static $default = 'default';

  /** @var string Mango_Database instance name */
  protected $_name;

  /** @var array Configuration */
  protected $_config = array();

  /** @var boolean Connection state */
  protected $_connected = FALSE;

  /** @var object The raw Mongo server connection */
  protected $_connection;

  /** @var MongoDB The database instance for the database name chosen by the config */
  protected $_db;

  /** Database name by default */
  const DB_NAME = 'Gleez';

  /** Module version */
  const VERSION = '0.1.1.3';

  /** Module name */
  const NAME = 'Mango Reader';

  /**
   * Creates a singleton of a Mango_Database group.
   * If no group is supplied the __default__ Mongo group is used.
   *
   * ### Create an instance of a group
   *
   * This code will assume the `my_group` configuration from
   * the `APPPATH/config/mongo.php`
   * (`MODPATH/<mango_dir>/config/mango.php` by default) file:<br>
   * <code>
   *   $foo_group = Mango::instance('my_group');
   * </code>
   *
   * Create an instance of the default group:<br>
   * <code>
   *   $default_group = Mango::instance();
   * </code>
   *
   * Alternatively it may be instantiated with the name and
   * configuration specified as arguments:<br>
   * <code>
   *  $my_group = Mango::instance('my_group', array(<br>
   *    'connection' => array(<br>
   *      'hostname' => '192.168.0.1',<br>
   *      'database' => 'Gleez'<br>
   *      'username'  => '...',<br>
   *    );<br><br>
   * ));
   * </code>
   *
   * Access an instantiated group directly:<br>
   * <code>
   *   $foo_group = Mango::$instances['foo'];
   * </code>
   *
   * @param   string    $group  Config group name [Optional]
   * @param   array     $config Pass a configuration array to bypass the Kohana config [Optional]
   * @return  Mango     MongoDB instance
   * @throws  Gleez_Exception
   */
  public static function instance($group = NULL, $config = NULL)
  {
    // If there is no group supplied
    if (is_null($group))
    {
      // Use the default setting
      $group = Mango::$default;
    }

    if (isset(Mango::$instances[$group]))
    {
      // Return the current group if initiated already
      return Mango::$instances[$group];
    }

    if (is_null($config))
    {
      // Load the configuration
      $config = Kohana::$config->load('mango');
    }

    if (! $config->offsetExists($group))
    {
      throw new Gleez_Exception('Failed to load :module group: :group',
        array(
          ':module' => self::NAME,
          ':group'  => $group
        )
      );
    }

    // Gets config group
    $config = $config->get($group);

    new Mango($group, $config);

    return Mango::$instances[$group];
  }

  /**
   * Class constructor
   *
   * @param   string    $name   Database instance name
   * @param   array     $config MongoDB config
   *
   * @throws  Kohana_Exception  In the absence of of mandatory configuration settings
   */
  protected function __construct($name, array $config)
  {

    // System Requirements check
    $this->system_check();

    $this->_name = $name;
    $this->_config = $config;

    $this->_db = isset($this->_config['connection.database'])
      ? $this->_config['connection.database']
      : Mango::DB_NAME;

    $host = isset($this->_config['connection.hostname'])
      ? $this->_config['connection.hostname']
      : NULL;

    $user = isset($this->_config['connection.username'])
      ? $this->_config['connection.username']
      : NULL;

    $passwd = isset($this->_config['connection.password'])
      ? $this->_config['connection.password']
      : NULL;

    $opt = Arr::get($this->_config['connection'], 'options', array());

    $prepared = $this->_prepare_connection($host, $user, $passwd);

    $this->_connection = new MongoClient($prepared, $opt);

    unset($host, $user, $passwd, $opt, $prepared);

    // Store the database instance
    Mango::$instances[$name] = $this;
  }

  /**
   * Getter and setter for the configuration.
   *
   * If no argument provided, the current configuration is returned.
   * Otherwise the configuration is set to this class.
   *
   * Overwrite all configuration:<br>
   * <code>
   *  $db->config(array(<br>
   *    'connection' => array(<br>
   *      'hostname' => '192.168.0.1',<br>
   *      'database'  => '...'<br>
   *    )<br>
   *  ));
   * </code>
   *
   * Modify exists or set a new configuration setting:<br>
   * <code>
   *  $db->config('connection', array(<br>
   *    'username' => 'gleez_user',<br>
   *  ));
   * </code>
   *
   * Get a configuration setting:<br>
   * <code>
   *  $config = $db->config('connection', 'hostname');<br>
   *  $config = $db->config('connection');<br>
   *  $config = $db->config();<br>
   * </code>
   *
   * @param   mixed $key    key to set to array, either array or config path
   * @param   mixed $value  value to associate with key
   * @uses    Arr::get
   *
   * @return  mixed
   */
  public function config($key = NULL, $value = NULL)
  {
    if (is_null($key))
    {
      return $this->_config;
    }

    if (is_array($key))
    {
      $this->_config = $key;
    }
    else
    {
      if (is_null($value))
      {
        return Arr::get($this->_config, $key, array());
      }

      if (is_array($value))
      {
        $this->_config = Arr::merge($this->_config, array($key => $value));
      }
      else
      {
        return Arr::get($this->_config[$key], $value);
      }
    }

    return $this;
  }

  /**
   * Check System Requirements
   *
   * @throws  Gleez_Exception   In the absence of the php-mongo extension
   * @throws  Gleez_Exception   When PHP version is not &gt;= 5.3
   * @throws  Gleez_Exception   When Gleez version is not &gt;= 0.9.8.2
   * @return  boolean
   */
  public function system_check()
  {
    if ( ! extension_loaded('mongo'))
    {
      throw new Gleez_Exception('The php-mongo extension is not installed or is disabled.');
    }

    if ( ! version_compare(PHP_VERSION, '5.3', '>='))
    {
      throw new Gleez_Exception(':module requires PHP 5.3 or newer, this version is :php_version.',
        array(
          ':module' => self::NAME,
          ':php_version' => PHP_VERSION
        )
      );
    }

    if ( ! version_compare(Gleez::VERSION, '0.9.8.2', '>='))
    {
      throw new Gleez_Exception(':module requires Gleez Core 0.9.8.2 or newer, this version is :gleez_version.',
        array(
          ':module' => self::NAME,
          ':gleez_version' => Gleez::VERSION
        )
      );
    }

    return TRUE;
  }

  /**
   * Class destructor
   */
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
      // can't throw exceptions in __destruct
    }
  }

  final public function __toString()
  {
    return $this->_name;
  }

  /**
   * Execute Command
   *
   * @param   string  $cmd    Command
   * @param   array   $args   Arguments [Optional]
   * @param   array   $values The values passed to the command [Optional]
   * @return  mixed   Responce the result of the method by passing in a `$cmd`
   * @return  boolean FALSE if command not found
   */
  public function _call($cmd, array $args = array(), $values = NULL)
  {
    // If there is no connection - we execute connect()
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
      case 'save':
        $responce = $c->save($args['criteria'], $args['options']);
      break;
      case 'drop_collection':
        $responce = $c->drop();
      break;
      default:
        $responce = FALSE;
    }

    return $responce;
  }

  /**
   * Prepare connection
   *
   * @param   string  $host   Database host
   * @param   string  $user   Database user name
   * @param   string  $passwd Database user password
   * @return  string
   */
  protected function _prepare_connection($host, $user, $passwd)
  {
    if (is_null($host))
    {
      $host = ini_get('mongo.default_host').':'.ini_get('mongo.default_port');
    }

    if ( ! is_null($user) AND ! is_null($passwd))
    {
      return 'mongodb://' . $user . ':' . $passwd . '@' . $host . '/' . $this->_db;
    }

    return 'mongodb://' . $host . '/' . $this->_db;
  }

  /**
   * Get an instance of MongoDB directly
   *
   * Example:<br>
   * <code>
   *  Mango::instance()->db();
   * </code>
   *
   * @return MongoDB
   */
  public function db()
  {
    $this->_connected OR $this->connect();

    return $this->_db;
  }

  /**
   * Database connection
   *
   * @return  TRUE              When the connection is successful
   * @throws  Kohana_Exception  When a connection error
   */
  public function connect()
  {
    // If no connection
    if(! $this->_connected)
    {
      try
      {
        // Connecting to the server
        $this->_connected = $this->_connection->connect();
      }
      catch (MongoConnectionException $e)
      {
        // Unable to connect to database server
        throw new Kohana_Exception('Unable to connect to MongoDB server. MongoDB said :message',
          array(
            ':message' => $e->getMessage()
          )
        );
      }

      $this->_db = $this->_connection->selectDB("$this->_db");
    }

    return $this->_connected;
  }

  /**
   * Disconnecting from the database
   *
   * @return boolean TRUE if successful, FALSE uf it fails
   */
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
   * Counting documents in a collection
   *
   * Example:<br>
   * <code>
   *  Mango::instance()->count('Users', array('sity' => 'Dubai')));
   * </code>
   *
   * @param   string  $collection Collection Name
   * @param   array   $query      NoSQL query [Optional]
   * @return  integer Amount of documents
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
   * Receives documents from the collection
   *
   * Example:<br>
   * <code>
   *  Mango::instance()->find('Users', array('sity' => 'Moscow'), array('email')));
   * </code>
   *
   * @param   string  $collection Collection Name
   * @param   array   $query      NoSQL query [Optional]
   * @param   array   $fields     Fields which are looking for in the request [Optional]
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
   * Gets 1 document from the collection
   *
   * Example:<br>
   * <code>
   *  Mango::instance()->find_one('Users', array('_id' => new MongoId($id)));
   * </code>
   *
   * @param   string  $collection Collection Name
   * @param   array   $query      NoSQL query [Optional]
   * @param   array   $fields     Fields which are looking for in the request [Optional]
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
   * Deleting a document from a collection
   *
   * Example:<br>
   * <code>
   *  Mango::instance()->remove('Users', array('name' => 'john'));
   * </code>
   *
   * @param   string  $collection Collection Name
   * @param   array   $criteria   The search criteria
   * @param   array   $options    Additional options [Optional]
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
   * Saves a document to this collection
   *
   * Example:<br>
   * <code>
   *  Mango::instance()->save('Guestbook', array('title' => '...', 'body' => '...'));
   * </code>
   *
   * @param   string  $collection Collection Name
   * @param   array   $criteria   The search criteria
   * @param   array   $options    Additional options [Optional]
   * @return  boolean|array
   *
   * @link    http://www.php.net/manual/ru/mongocollection.save.php
   */
  public function save($collection, array $criteria, $options = array())
  {
    return$this->_call('save', array(
      'collection'  => $collection,
      'criteria'    => $criteria,
      'options'     => $options
    ));
  }

  /**
   * Drop collection
   *
   * Example:<br>
   * <code>
   *  Mango::instance()->drop_collection('Logs');
   * </code>
   *
   * @param   string  $collection Collection Name
   * @return  array   The database response as array
   *
   * @link    http://php.net/manual/en/mongocollection.drop.php MongoCollection::drop()
   */
  public function drop_collection($collection)
  {
    return $this->_call('drop_collection', array(
      'collection'  => $collection
    ));
  }

  /**
   * Batch insert in a collection
   *
   * Example:<br>
   * <code>
   *  Mango::instance()->batch_insert('Users', array('name' => 'john', 'passwd' => '...'));
   * </code>
   *
   * Note: If in the array `$a` pass the objects,
   * they should not have the properties of `protected` or `private`
   *
   * @param   string      $collection Collection Name
   * @param   array       $a          An array of arrays or objects
   * @return  mixed
   *
   * @link    http://php.net/manual/en/mongocollection.batchinsert.php MongoCollection::batchInsert()
   */
  public function batch_insert($collection, array $a)
  {
    return $this->_call('batch_insert', array('collection' => $collection), $a);
  }

}
