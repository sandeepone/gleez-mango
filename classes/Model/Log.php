<?php
/**
 * Gleez CMS (http://gleezcms.org)
 *
 * @link https://github.com/cleez/cms Canonical source repository
 * @copyright Copyright (c) 2011-2014 Gleez Technologies
 * @license http://gleezcms.org/license Gleez CMS License
 */

use \Gleez\Mango\Document;

/**
 * Log model
 *
 * @property \MongoId $_id
 * @property \MongoId $id Alias for $_id
 * @property string $level
 * @property string $hostname
 * @property string $user_agent
 * @property string $file
 * @property string $line
 * @property string $class
 * @property string $function
 * @property string $url
 * @property string $referer
 * @property string $body
 */
class Model_Log extends Document
{
	/**
	 * Collection name
	 * @var string
	 */
	protected $name = 'logs';
}
