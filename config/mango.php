<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Основные настройки модуля Mango Reader
 *
 * @package   Mango
 * @category  Settings
 * @author    Яковлев Сергей (me@klay.me)
 * @version   0.1.1.1
 * @copyright (c) 2013 Яковлев Сергей
 * @license   GPLv3
 */

return array(
  /**
   * @var array Название конфигурации
   *
   * Используется при инициализации нового экземпляра MongoDB
   *
   *    // Пример использования:
   *    $db = Mango::instance('default');
   */
  'default' => array(

    /** @var array Настройки соединения */
    'connection' => array(

      /** @var string Хост базы данных */
      //'hostname' => '192.168.0.1',

      /** @var string Имя базы данных */
      //'database'  => 'Cerber',

      /** @var string Аутентификация */
      //'username'  => 'username',
      //'password'  => 'password',

      /** @var array Дополнительные опции */
      //'options'   => array(
      //  'persist'    => 'persist_id',
      //  'timeout'    => 1000,
      //  'replicaSet' => TRUE
      //)
    ),
  )
);