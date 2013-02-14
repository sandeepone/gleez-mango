<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * [Gleez Cache](api/Gleez_Cache) MongoDB driver
 *
 * ### Configuration example
 *
 * <code>
 *  return array(<br>
 *    'mango' => array( // Driver group<br>
 *      'driver' => 'mango', // Using Mango driver<br>
 *      'collection' => 'Cache', // Cahce collection<br>
 *      'default_expire' => 3600 // Default expire<br>
 *    )<br>
 *  );
 * <code>
 *
 * ### System Requirements
 * - PHP 5.3 or higher
 * - Gleez CMS 0.9.8.2 or higher
 * - MondoDB 2.3.3 or higher
 * - PHP-extension MongoDB 1.3 or higher
 *
 * @package   Mango\Cache
 * @author    Sergey Yakovlev - Gleez
 * @copyright (c) 2011-2013 Gleez Technologies
 * @license   http://gleezcms.org/license
 */
class Mango_Cache_Mango extends Cache implements Cache_Arithmetic, Mango_Core {

  /** @var MongoDB The database instance for the db name chosen by the config */
  protected $_db;

  /** @var string Cahce collection */
  protected $_collection = 'Cache';

  /** @var integer Default expire */
  protected $_expire = NULL;

  /**
   * Check system requirements, initiate default values
   *
   * This method cannot be invoked externally.
   * The driver must be instantiated using the <code>Cache::instance()</code>
   * method.
   *
   * @param array $config
   */
  protected function __construct(array $config)
  {
    $this->system_check();

    $this->_db = Mango::instance();

    if($config['collection'])
    {
      $this->_collection = $config['collection'];
    }

    if($config['default_expire'])
    {
      $this->_expire = $config['default_expire'];
    }

    parent::__construct($config);
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
   * Set a value to cache with id and lifetime
   *
   * Set 'bar' to 'foo' in mango group for 10 minutes
   * <code>
   * <br>$data = 'bar';<br>
   * if (Cache::instance('mango')->set('foo', $data, 600))<br>
   * {<br>
   *  // Cache was set successfully<br>
   *  return TRUE;<br>
   * }
   * </code>
   *
   * ### Note
   *
   * The config <code>defualt_expire</code> variable value is the highest
   * priority, if the variable exists and is not NULL
   *
   * @param   string  $id       ID of cache entry
   * @param   mixed   $data     Data to set to cache
   * @param   integer $lifetime Lifetime in seconds [Optional]
   * @return  boolean
   */
  public function set($id, $data, $lifetime = 3600)
  {
    if(Kohana::$environment == Kohana::DEVELOPMENT)
    {
      return FALSE;
    }

    // Setup lifetime
    if(is_null($this->_expire))
    {
      $lifetime = (0 === $lifetime) ? 0 : $lifetime + time();
    }
    else
    {
      $lifetime == $this->_expire;
    }

    $scalar = is_scalar($data);

    $entry = array(
      '_id'         => (string)$this->_sanitize_id($id),
      'cid'         => (string)$this->_sanitize_id($id),
      'cache'       => $scalar ? $data : serialize($data),
      'expiration'  => $lifetime,
    );

    // Delete if exists - Besure to avoid conflict
    $this->delete($id);

    // Set the data to mongodb
    return $this->_db->save($this->_collection, $entry);
  }

  /**
   * Retrieve a cached value entry by id
   *
   * Retrieve cache entry from foo group:<br>
   * <code>
   *  $data = Cache::instance('mango')->get('foo');
   * </code>
   *
   * Retrieve cache entry from foo group and return 'bar' if miss:<br>
   * <code>
   *  $data = Cache::instance('mongo')->get('foo', 'bar');
   * </code>
   *
   * @param   string  $id       ID of cache to entry
   * @param   string  $default  Default value to return if cache miss
   * @return  mixed
   */
  public function get($id, $default = NULL)
  {
    if(Kohana::$environment == Kohana::DEVELOPMENT)
    {
      return FALSE;
    }

    // Get the value from Mongodb
    $result = $this->_db->find_one($this->_collection, array('_id' => (string)$this->_sanitize_id($id)));

    if ( ! $result || ! isset($result['cache']))
    {
      return FALSE;
    }

    $result = (object)$result;

    // If the cache has expired
    if ($result->expiration != 0 AND $result->expiration <= time())
    {
      // Delete it and return default value
      $this->delete($id);
      return $default;
    }
    else
    {
      // Return the valid cache data
      $data = unserialize($result->cache);
    }

    // Return the value
    return $data;
  }

  /**
   * Delete a cache entry based on id
   *
   * Delete the 'foo' cache entry immediately:<br>
   * <code>
   *  Cache::instance('mongo')->delete('foo');
   * </code>
   *
   * @param   string $id  ID of entry to delete
   * @return  mixed
   */
  public function delete($id)
  {
    // Delete thedocument by id
    return $this->_db->remove($this->_collection, array('_id' => $this->_sanitize_id($id)));
  }

  /**
   * Delete all cache entries
   *
   * Beware of using this method when using shared memory cache systems,
   * as it will wipe every entry within the system for all clients.
   *
   * Delete all cache entries in the `mango` group:<br>
   * <code>
   *  Cache::instance('mango')->delete_all();
   * </code>
   *
   * @return type
   */
  public function delete_all()
  {
    return $this->_db->drop_collection($this->_collection);
  }

  public function increment($id, $step = 1){}

  public function decrement($id, $step = 1){}

}