<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Класс записи событий в журнал, используя базу данных MongoDB
 *
 * ### Системные требования
 *
 *  - PHP 5.3 или старше
 *  - PHP-расширение Mongodb 1.3 или старше
 *  - Модуль Mango Reader 0.1.1.1 или старше
 *
 * @package   Cerber
 * @category  Logging/Mango
 * @author    Яковлев Сергей (me@klay.me)
 * @version   0.1.1.1
 * @copyright (c) 2013 Яковлев Сергей
 * @license   GPLv3
 */

class Mango_Log_Mango extends Log_Writer {

  /**
   * @var string Коллекция для записи журнала
   *
   * Используйте коллекцию фиксированного размера, для поддержки высокой
   * пропускной способности операций вставки
   * @link http://docs.mongodb.org/manual/core/capped-collections/ Capped Collections
   */
  protected $_collection;

  /** @var string Название экземпляра базы данных */
  protected $_name;

  /**
   * Конструктор класса
   *
   *    // Пример использования
   *    $writer = new Log_Mango($collection);
   *
   * @param   string  $collection Название коллекции [Опционально]
   * @param   string  $name       Название экземпляра базы данных [Опционально]
   */
  public function __construct($collection = 'Logs', $name = 'default')
  {
    $this->_collection  = $collection;
    $this->_name        = $name;
  }

  /**
   * Запись сообщений в MongoDB коллекцию
   *
   * @param   array   $messages   Массив сообщений
   */
  public function write(array $messages)
  {
    // Описательный массив
    $info = array
    (
      'host'  => Request::$client_ip,
      'agent' => Request::$user_agent,
      'user'  => User::active_user()->id,
      'url'   => Text::plain(Request::initial()->uri()),
    );

    // Сообщение для записи в журнал
    $logs = array();

		foreach ($messages as $message)
		{
      if(isset($message))
      {
        $message['type']  = $this->_log_levels[$message['level']];
        $message['time']  = new MongoDate(strtotime($message['time']));

        // Слияние описательного массива и текущего сообщения
        $logs[] = array_merge($info, $message);
      }
    }

    if(! empty($logs))
    {
      // Запись сообщения в коллекцию
      Mango::instance($this->_name)->batch_insert($this->_collection, $logs);
    }
  }

}