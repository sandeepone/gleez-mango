<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Mango Reader Core Interface
 *
 * ### System Requirements
 * - PHP 5.3 or higher
 * - Gleez CMS 0.9.8.2 or higher
 * - MondoDB 2.3.3 or higher
 * - PHP-extension MongoDB 1.3 or higher
 *
 * @package   Mango\Core
 * @author    Sergey Yakovlev - Gleez
 * @copyright (c) 2011-2013 Gleez Technologies
 * @license   http://gleezcms.org/license
 * @since     0.1.1.3
 */
interface Mango_Core {

  /**
   * Check System Requirements
   *
   * @throws  Gleez_Exception   In the absence of the php-mongo extension
   * @throws  Gleez_Exception   When PHP version is not &gt;= 5.3
   * @throws  Gleez_Exception   When Gleez version is not &gt;= 0.9.8.2
   * @return  boolean
   */
  public function system_check();
}